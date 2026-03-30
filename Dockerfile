# =========================
# BASE (sua imagem base)
# =========================
FROM bhcosta90/bhcosta90:base:base AS base

WORKDIR /var/www/html

# =========================
# DEPENDENCIES (COMPOSER)
# =========================
FROM base AS vendor

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-scripts \
    --optimize-autoloader

# =========================
# ASSETS (NODE / VITE)
# =========================
FROM node:20-alpine AS node_builder

WORKDIR /var/www/html

# Instala dependências do node
COPY package.json package-lock.json* ./
RUN npm install

# 🔥 IMPORTANTE: traz o vendor pro build do vite
COPY --from=vendor /var/www/html/vendor /var/www/html/vendor

# Copia o resto do projeto
COPY . .

# Variável padrão para build
ENV NODE_ENV=production

# Build dos assets
RUN npm run build

# =========================
# APP (preparação final)
# =========================
FROM base AS app

# Copia dependências PHP
COPY --from=vendor /var/www/html/vendor /var/www/html/vendor

# Copia assets buildados
COPY --from=node_builder /var/www/html/public/build /var/www/html/public/build

# Copia aplicação
COPY . .

# Permissões
RUN mkdir -p storage/framework/cache/data \
             storage/framework/sessions \
             storage/framework/views \
             storage/logs \
             bootstrap/cache

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# EntryPoint
RUN chmod +x ./entrypoint.sh

ENTRYPOINT ["./entrypoint.sh"]

# =========================
# FINAL (imagem leve)
# =========================
FROM base

WORKDIR /var/www/html

COPY --from=app /var/www/html /var/www/html

RUN chmod +x ./entrypoint.sh

EXPOSE 80

ENTRYPOINT ["./entrypoint.sh"]