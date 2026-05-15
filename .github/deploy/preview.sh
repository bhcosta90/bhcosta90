#!/bin/bash

set -e

ACTION="${1:-}"
BRANCH_SLUG="${2:-}"
BRANCH_REF="${3:-$BRANCH_SLUG}"
COMMIT_SHA="${4:-}"

BASE_DIR="${BASE_DIR:-$(pwd)}"
REPO_NAME="$(basename "$BASE_DIR")"
ACCOUNT="$(basename "$(dirname "$BASE_DIR")")"
REPO="${REPO:-git@github.com:bhcosta90/bhcosta90.git}"

DOMAIN="${DOMAIN:-${REPO_NAME}.bhcosta90.dev.br}"
CADDYFILE="${CADDYFILE:-/etc/caddy/Caddyfile}"

ENV_DIR="$BASE_DIR/.env"
PREVIEW_DIR="$BASE_DIR/previews/$BRANCH_SLUG"

SUPERVISOR_CONF="/etc/supervisor/conf.d/${ACCOUNT}-${REPO_NAME}-${BRANCH_SLUG}-horizon.conf"
SUPERVISOR_PROGRAM="${ACCOUNT}-${REPO_NAME}-${BRANCH_SLUG}-horizon"
PREVIEW_DOMAIN="${BRANCH_SLUG}-preview.${DOMAIN}"

if [[ -z "$ACTION" || -z "$BRANCH_SLUG" ]]; then
    echo "usage: preview.sh open|close <branch-slug> [<branch-ref>] [<commit-sha>]"
    exit 1
fi

echo "🔎 Preview: $ACTION | $BRANCH_SLUG"

# ================================
# 🔁 FUNÇÕES
# ================================

build() {
    export COMPOSER_ALLOW_SUPERUSER=1
    composer install --no-dev --optimize-autoloader --no-interaction
}

build_frontend() {
    echo "🎨 Build frontend (Vite)"
    npm install
    npm run build
}

generate_env() {
    echo "🧪 Gerando .env do preview"

    BASE="$ENV_DIR/.env.base"

    mkdir -p "$ENV_DIR"
    [[ -f "$BASE" ]] || touch "$BASE"

    # Overlay: .env.{branch-slug} tem prioridade; fallback .env.develop
    if [[ -f "$ENV_DIR/.env.$BRANCH_SLUG" ]]; then
        OVERLAY="$ENV_DIR/.env.$BRANCH_SLUG"
        echo "🎯 Override específico: .env.$BRANCH_SLUG"
    else
        [[ -f "$ENV_DIR/.env.develop" ]] || touch "$ENV_DIR/.env.develop"
        OVERLAY="$ENV_DIR/.env.develop"
        echo "↪ Fallback: .env.develop"
    fi

    cp "$BASE" .env

    while IFS= read -r line || [ -n "$line" ]; do
        [[ -z "$line" || "$line" =~ ^# ]] && continue
        KEY=$(echo "$line" | cut -d '=' -f 1)
        if grep -q "^$KEY=" .env; then
            sed -i "s|^$KEY=.*|$line|" .env
        else
            echo "$line" >> .env
        fi
    done < "$OVERLAY"
}

ensure_prefixes() {
    echo "🔧 Prefixos de isolamento (preview)"

    PREFIX="preview_${BRANCH_SLUG}_"

    replace_or_add() {
        KEY=$1
        VALUE=$2
        if grep -q "^$KEY=" .env; then
            sed -i "s|^$KEY=.*|$KEY=$VALUE|" .env
        else
            echo "$KEY=$VALUE" >> .env
        fi
    }

    replace_or_add "APP_NAME"       "\"${REPO_NAME}-preview-${BRANCH_SLUG}\""
    replace_or_add "REDIS_PREFIX"   "${PREFIX}"
    replace_or_add "CACHE_PREFIX"   "${PREFIX}cache_"
    replace_or_add "SESSION_PREFIX" "${PREFIX}session_"
    replace_or_add "HORIZON_PREFIX" "${PREFIX}horizon:"
}

prepare_storage() {
    mkdir -p storage/logs storage/framework storage/app
    chmod -R 777 storage bootstrap/cache
    chown -R server:server storage bootstrap/cache
}

optimize() {
    php artisan optimize
}

migrate_db() {
    php artisan migrate --force
    php artisan permissions:refresh --force
}

supervisor_up() {
    echo "⚙️ Supervisor up ($SUPERVISOR_PROGRAM)"

    sudo -n tee "$SUPERVISOR_CONF" > /dev/null <<EOF
[program:$SUPERVISOR_PROGRAM]
process_name=%(program_name)s
command=php $PREVIEW_DIR/artisan horizon
autostart=true
autorestart=true
user=server
redirect_stderr=true
stdout_logfile=$PREVIEW_DIR/storage/logs/horizon.log
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

supervisor_down() {
    if [[ -f "$SUPERVISOR_CONF" ]]; then
        echo "🛑 Supervisor down ($SUPERVISOR_PROGRAM)"
        sudo -n supervisorctl stop "$SUPERVISOR_PROGRAM" || true
        sudo -n rm -f "$SUPERVISOR_CONF"
        sudo -n supervisorctl reread || true
        sudo -n supervisorctl update "$SUPERVISOR_PROGRAM" || true
    fi
}

caddy_down() {
    if sudo -n grep -q "# --- preview:${BRANCH_SLUG} --- start" "$CADDYFILE" 2>/dev/null; then
        echo "🌐 Caddy down (preview:${BRANCH_SLUG})"
        sudo -n sed -i "/# --- preview:${BRANCH_SLUG} --- start/,/# --- preview:${BRANCH_SLUG} --- end/d" "$CADDYFILE"
        sudo -n systemctl reload caddy
    fi
}

caddy_up() {
    echo "🌐 Caddy up (http://${PREVIEW_DOMAIN})"

    # garante idempotência: limpa bloco antigo se existir
    caddy_down

    sudo -n tee -a "$CADDYFILE" > /dev/null <<EOF

# --- preview:${BRANCH_SLUG} --- start
http://${PREVIEW_DOMAIN} {
    root * ${PREVIEW_DIR}/public
    php_fastcgi unix//run/php/php8.3-fpm.sock
    file_server
}
# --- preview:${BRANCH_SLUG} --- end
EOF

    # valida antes do reload — se ficou inválido, reverte e aborta
    # (outros sites em produção seguem intactos na config antiga)
    if ! sudo -n caddy validate --config "$CADDYFILE" --adapter caddyfile > /dev/null 2>&1; then
        echo "❌ Caddyfile inválido após append — revertendo"
        caddy_down
        exit 1
    fi

    sudo -n systemctl reload caddy
}

# ================================
# ▶ AÇÕES
# ================================

open_preview() {
    # reabrindo? limpa tudo antes
    if [[ -d "$PREVIEW_DIR" ]]; then
        supervisor_down
        caddy_down
        rm -rf "$PREVIEW_DIR"
    fi

    mkdir -p "$BASE_DIR/previews"

    echo "📥 Clonando $BRANCH_REF em $PREVIEW_DIR"
    git clone --depth 1 --branch "$BRANCH_REF" "$REPO" "$PREVIEW_DIR"
    cd "$PREVIEW_DIR"

    if [[ -n "$COMMIT_SHA" ]]; then
        echo "📌 Checkout commit $COMMIT_SHA"
        git fetch origin "$COMMIT_SHA"
        git checkout "$COMMIT_SHA"
    fi

    echo "📦 Composer"
    build

    echo "🎨 Frontend"
    build_frontend

    echo "🧪 ENV"
    generate_env
    ensure_prefixes

    echo "📁 Storage"
    prepare_storage

#    echo "🧹 Cache"
#    optimize

    echo "🧱 Migrate"
    migrate_db

    echo "📬 Horizon via Supervisor"
    supervisor_up

    echo "🌐 Caddy"
    caddy_up

    echo "✅ Preview $BRANCH_SLUG pronto em $PREVIEW_DIR"
    echo "🔗 http://${PREVIEW_DOMAIN}"
}

close_preview() {
    supervisor_down
    caddy_down
    rm -rf "$PREVIEW_DIR"
    echo "✅ Preview $BRANCH_SLUG removido"
}

case "$ACTION" in
    open)  open_preview ;;
    close) close_preview ;;
    *)     echo "❌ Ação inválida: $ACTION"; exit 1 ;;
esac
