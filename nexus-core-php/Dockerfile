# === Estágio 1: Instalação de Dependências ===
FROM composer:2.8 AS vendor

WORKDIR /app

# Copia os arquivos de dependência do PHP
COPY composer.json composer.lock ./

# Instala pacotes de produção limpando o cache e ignorando scripts de desenvolvimento
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist

# === Estágio 2: Execução em Produção ===
FROM php:8.3-cli-alpine

WORKDIR /var/www/html

# Instala extensões nativas do PHP necessárias para o ecossistema (como pdo_mysql ou pdo_pgsql)
RUN docker-php-ext-install pdo pdo_mysql

# Copia o código fonte da aplicação PHP para o container
COPY . .

# Copia as dependências da pasta vendor instaladas no estágio anterior
COPY --from=vendor /app/vendor ./vendor

# Otimiza o mapa de classes de autoload para performance máxima em produção
COPY --from=vendor /usr/bin/composer /usr/bin/composer
RUN composer dump-autoload --optimize --no-dev

# Como rodaremos via Worker assíncrono consumindo do Redis
CMD ["php", "artisan", "queue:work"]
