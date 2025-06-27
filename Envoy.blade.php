@setup
    $app_dir = '/var/www/bhcosta90.dev.br/';
    $branch = 'main';
@endsetup

@php
    Dotenv\Dotenv::createImmutable(__DIR__)->load();
    $webServer = explode(',', $_ENV['DEPLOY_SERVERS_WEB']);
@endphp

@servers(['web' => $webServer])

@story('deploy', ['on' => 'web'])
    pause-horizon
    update-code
    node-install-dependencies
    php-install-dependencies
    php-artisan-config-cache
    php-artisan-migrate
    start-horizon
@endstory

@story('reset', ['on' => 'web'])
    pause-horizon
    update-code
    reset-database
    php-install-dependencies
    php-artisan-config-cache
    start-horizon
@endstory

@task('pause-horizon')
    cd {{ $app_dir }}
    echo "🔄 Iniciando deploy..."

    echo "⏸️ Pausando Horizon..."
    php artisan horizon:pause

    echo "⏳ Aguardando jobs em execução..."
    while php artisan horizon:status | grep -q running; do
        echo "⏳ Ainda processando jobs... aguardando 5s"
    sleep 5
    done

    git pull origin {{ $branch }}
@endtask

@task('start-horizon')
    cd {{ $app_dir }}
    echo "♻️ Reiniciando Horizon..."
    php artisan horizon:terminate

    echo "▶️ Voltando Horizon ao normal..."
    php artisan horizon:continue

    echo "✅ Deploy finalizado com sucesso!"
@endtask

@task('update-code')
    cd {{ $app_dir }}
    git checkout {{ $branch }} -f
    git pull origin {{ $branch }}
@endtask

@task('php-install-dependencies')
    cd {{ $app_dir }}
    if [ $(find composer.json -mmin -1 | wc -l) -eq 0 ]; then
        composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --no-scripts --optimize-autoloader
    else
        echo "composer.json it was modified less than 1 minute. We are jumping this installation process."
    fi
    rm -f bootstrap/cache/{config.php,events.php,packages.php,routes-v7.php,services.php}
@endtask

@task('node-install-dependencies')
    cd /var/www/bhcosta90.dev.br
    if [ $(find package.json -mmin -1 | wc -l) -eq 0 ]; then
        npm install
    else
        echo "package.json it was modified less than 1 minute. We are jumping this installation process."
    fi
    npm run build
@endtask

@task('php-artisan-migrate')
    cd {{ $app_dir }}
    php artisan migrate --force
@endtask

@task('reset-database')
    cd {{ $app_dir }}
    composer install
    php artisan migrate:fresh --seed --force
@endtask

@task('php-artisan-config-cache')
    cd {{ $app_dir }}
    php artisan config:cache
    php artisan route:cache
    php artisan event:cache
    php artisan view:cache
@endtask

@task('remove-log')
    cd {{ $app_dir }}
    rm -f storage/logs/*.log
@endtask
