FROM bhcosta90/bhcosta90:base AS base
WORKDIR /var/www/html

# =========================
# NODE BUILD
# =========================
FROM base AS node_builder

RUN apk add --no-cache nodejs npm

COPY package.json package-lock.json* ./
RUN npm ci

COPY . .
RUN npm run build

# =========================
# PHP DEPENDENCIES
# =========================
FROM base AS composer_builder

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-scripts \
    --optimize-autoloader \
    --classmap-authoritative

# =========================
# FINAL
# =========================
FROM base

WORKDIR /var/www/html

COPY --from=composer_builder /var/www/html/vendor ./vendor
COPY --from=node_builder /var/www/html/public/build ./public/build
COPY . .

RUN chmod +x ./entrypoint.sh

RUN mkdir -p storage/framework/cache/data \
             storage/framework/sessions \
             storage/framework/views \
             storage/logs \
             bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80

ENTRYPOINT ["./entrypoint.sh"]