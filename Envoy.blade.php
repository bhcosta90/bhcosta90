@php
    Dotenv\Dotenv::createImmutable(__DIR__)->load();
    $webServer = explode(',', $_ENV['DEPLOY_SERVERS_WEB']);
@endphp

@servers(['web' => $webServer])

@story('deploy', ['on' => 'web'])
    update-code
    install-dependencies
    php-artisan-config-cache
@endstory

@task('update-code')
    cd /var/www/bhcosta90.dev.br/
    git pull origin main
@endtask

@task('install-dependencies')
    cd /var/www/bhcosta90.dev.br/
    rm bootstrap/cache/{config.php,events.php,packages.php,routes-v7.php,services.php}
    composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --no-scripts --optimize-autoloader
    npm install
    npm run build
@endtask

@task('php-artisan-config-cache')
    cd /var/www/bhcosta90.dev.br/
    php artisan config:cache
    php artisan route:cache
    php artisan event:cache
    php artisan view:cache
@endtask
