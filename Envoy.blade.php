@servers(['web' => ['root@143.110.156.127']])

@story('deploy')
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
    composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --no-scripts --optimize-autoloader
@endtask

@task('php-artisan-config-cache')
    cd /var/www/bhcosta90.dev.br/
    php artisan config:cache
    php artisan route:cache
    php artisan event:cache
    php artisan view:cache
@endtask
