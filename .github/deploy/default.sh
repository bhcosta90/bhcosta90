#!/bin/bash

set -e

MODE=${1:-release}
ENVIRONMENT=${2:-production}

BASE_DIR="${BASE_DIR:-$(pwd)}"
APP_DIR="$BASE_DIR/$ENVIRONMENT"
ENV_DIR="$BASE_DIR/.env"
REPO_NAME="$(basename "$BASE_DIR")"
ACCOUNT="$(basename "$(dirname "$BASE_DIR")")"

REPO="${REPO:-git@github.com:bhcosta90/wms.git}"
BRANCH="$ENVIRONMENT"

LOCK_FILE="$APP_DIR/deploy.lock"

SUPERVISOR_CONF="/etc/supervisor/conf.d/${ACCOUNT}-${REPO_NAME}-${ENVIRONMENT}-horizon.conf"
SUPERVISOR_PROGRAM="${ACCOUNT}-${REPO_NAME}-${ENVIRONMENT}-horizon"

echo "🚀 Deploy: $MODE | $ENVIRONMENT"

# ================================
# 📁 GARANTE DIRETÓRIO
# ================================
mkdir -p "$APP_DIR"

# ================================
# 🔒 LOCK
# ================================
if [ -f "$LOCK_FILE" ]; then
    echo "❌ Deploy já está em andamento!"
    exit 1
fi

touch "$LOCK_FILE"
trap "rm -f $LOCK_FILE" EXIT

# ================================
# 🔁 FUNÇÕES
# ================================

build() {
    export COMPOSER_ALLOW_SUPERUSER=1
    composer install --no-dev --optimize-autoloader --no-interaction
}

generate_version_file() {
    echo "📝 Gerando version.txt"

    # hash curto (6 dígitos)
    COMMIT=$(git rev-parse --short=6 HEAD)

    # email do autor
    EMAIL=$(git log -1 --pretty=format:'%ae')

    # máscara do email (ex: br***@gmail.com)
    MASKED_EMAIL=$(echo "$EMAIL" | sed -E 's/(^..).*(.@.*)/\1***\2/')

    echo "$COMMIT - $MASKED_EMAIL" > version.txt
}

generate_env() {
    echo "🧪 Gerando .env (merge inteligente)"

    BASE="$ENV_DIR/.env.base"
    ENV="$ENV_DIR/.env.$ENVIRONMENT"

    # Bootstrap: garante que os arquivos existam (primeiro deploy de projeto novo)
    mkdir -p "$ENV_DIR"
    [[ -f "$BASE" ]] || touch "$BASE"
    [[ -f "$ENV" ]]  || touch "$ENV"

    # começa com base
    cp "$BASE" .env

    # percorre env específico
    while IFS= read -r line || [ -n "$line" ]; do

        # ignora linhas vazias e comentários
        [[ -z "$line" || "$line" =~ ^# ]] && continue

        KEY=$(echo "$line" | cut -d '=' -f 1)

        if grep -q "^$KEY=" .env; then
            # substitui valor existente
            sed -i "s|^$KEY=.*|$line|" .env
        else
            # adiciona nova chave
            echo "$line" >> .env
        fi

    done < "$ENV"
}

build_frontend() {
    echo "🎨 Build frontend (Vite)"

    npm install
    npm run build
}

ensure_prefixes() {
    echo "🔧 Garantindo prefixes no .env"

    add_if_missing() {
        KEY=$1
        VALUE=$2

        if ! grep -q "^$KEY=" .env; then
            echo "$KEY=$VALUE" >> .env
            echo "➕ $KEY adicionado"
        fi
    }

    PREFIX="wms_${ENVIRONMENT}_"

    add_if_missing "REDIS_PREFIX" "$PREFIX"
    add_if_missing "CACHE_PREFIX" "$PREFIX"
    add_if_missing "SESSION_PREFIX" "$PREFIX"
    add_if_missing "HORIZON_PREFIX" "$PREFIX"
}

prepare_storage() {
    mkdir -p storage/logs storage/framework storage/app
    chmod -R 777 storage bootstrap/cache
    chown -R server:server storage bootstrap/cache
}

optimize() {
    php artisan optimize
}

validate() {
    php artisan --version > /dev/null
    php artisan migrate --pretend --force > /dev/null
    php artisan tenants:migrate --pretend --force > /dev/null
}

migrate_db() {
    php artisan migrate --force
    php artisan tenants:migrate --force
    php artisan permissions:refresh --force
}

supervisor_up() {
    echo "⚙️ Supervisor up ($SUPERVISOR_PROGRAM)"

    local artisan_path
    if [[ "$MODE" == "release" ]]; then
        artisan_path="$APP_DIR/current/artisan"
    else
        artisan_path="$APP_DIR/artisan"
    fi

    sudo -n tee "$SUPERVISOR_CONF" > /dev/null <<EOF
[program:$SUPERVISOR_PROGRAM]
process_name=%(program_name)s
command=php $artisan_path horizon
autostart=true
autorestart=true
user=server
redirect_stderr=true
stdout_logfile=/var/log/supervisor/$SUPERVISOR_PROGRAM.log
stopwaitsecs=3600
EOF

    sudo -n supervisorctl reread || true
    sudo -n supervisorctl update "$SUPERVISOR_PROGRAM" || true

    if ! sudo -n supervisorctl status "$SUPERVISOR_PROGRAM" >/dev/null 2>&1; then
        echo "❌ Programa $SUPERVISOR_PROGRAM não foi registrado pelo supervisor"
        exit 1
    fi

    if sudo -n supervisorctl status "$SUPERVISOR_PROGRAM" 2>/dev/null | grep -q RUNNING; then
        echo "✅ $SUPERVISOR_PROGRAM já está em execução"
    else
        sudo -n supervisorctl start "$SUPERVISOR_PROGRAM" || true
    fi
}

restart_queue() {
    if php artisan horizon:status >/dev/null 2>&1; then
        echo "🚀 Horizon detected"

        echo "⏸️ Pausing Horizon..."
        php artisan horizon:pause || true

        echo "⏳ Waiting for running jobs to finish..."
        while php artisan horizon:status | grep -q "running"; do
            echo "⏳ Still processing jobs... waiting 5s"
            sleep 5
        done

        echo "♻️ Restarting Horizon..."
        php artisan horizon:terminate || true

    else
        echo "📬 Using default queue"
        php artisan queue:restart || true
    fi
}

run_pipeline() {
    echo "📦 Composer"
    build

    echo "🎨 Frontend"
    build_frontend

    echo "🧪 ENV"
    generate_env
    ensure_prefixes

    echo "📁 Storage"
    prepare_storage

    echo "🧹 Cache"
    optimize

#    echo "🔍 Validação"
#    validate

#    echo "🧱 Migrate"
#    migrate_db
#
#    echo "📬 Queue"
#    restart_queue

    echo "📝 Version"
    generate_version_file
}

rollback() {
    RELEASES_DIR="$APP_DIR/releases"
    cd $RELEASES_DIR

    RELEASES=($(ls -dt */))

    if [ ${#RELEASES[@]} -lt 2 ]; then
        echo "❌ Não há release anterior"
        exit 1
    fi

    PREVIOUS="${RELEASES[1]}"

    echo "🔙 Rollback para: $PREVIOUS"

    ln -sfn "$RELEASES_DIR/$PREVIOUS" "$APP_DIR/current"

    cd "$APP_DIR/current"

    optimize
    restart_queue
}

# ================================
# 🔙 ROLLBACK
# ================================
if [ "$MODE" = "rollback" ]; then
    rollback
    echo "✅ Rollback concluído!"
    exit 0
fi

# ================================
# 🚀 PREPARAR CÓDIGO
# ================================

if [ "$MODE" = "release" ]; then

    if [ "$ENVIRONMENT" = "production" ] && [[ "$BRANCH" != "main" && "$BRANCH" != "master" ]]; then
        echo "❌ Produção só pode usar main ou master"
        exit 1
    fi

    RELEASES_DIR="$APP_DIR/releases"

    TIMESTAMP=$(date +%Y%m%d%H%M%S)
    NEW_RELEASE="$RELEASES_DIR/$TIMESTAMP"

    echo "📥 Clonando repositório"
    git clone --depth=1 --branch=$BRANCH $REPO $NEW_RELEASE

    cd $NEW_RELEASE

elif [ "$MODE" = "unique" ]; then

    cd $APP_DIR

    if [ ! -d ".git" ]; then
        echo "📥 Clonando repositório (primeira vez)"
        rm -rf *
        git clone --branch=$BRANCH $REPO .
    else
        echo "📥 Atualizando código"
        git fetch origin
        git reset --hard origin/$BRANCH
    fi

else
    echo "❌ Modo inválido"
    exit 1
fi

# ================================
# ⚙️ PIPELINE
# ================================

run_pipeline

# ================================
# 🧩 FINALIZAÇÃO
# ================================

if [ "$MODE" = "release" ]; then
    echo "🔄 Ativando nova versão"
    ln -sfn $NEW_RELEASE $APP_DIR/current

    echo "⚙️ Supervisor"
    supervisor_up

    echo "🔍 Verificando aplicação"

    echo "🧹 Limpando releases antigas"
    cd $RELEASES_DIR
    ls -dt */ | tail -n +2 | xargs rm -rf || true
elif [ "$MODE" = "unique" ]; then
    echo "⚙️ Supervisor"
    supervisor_up
fi

echo "✅ Deploy finalizado!"
