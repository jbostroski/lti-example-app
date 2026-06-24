# ---------- STAGE 1: build dos assets ----------
FROM node:24 AS assets

WORKDIR /app

COPY package*.json ./
RUN npm install

COPY resources ./resources
COPY vite.config.* ./
COPY public ./public

RUN npm run build


# ---------- STAGE 2: aplicação PHP ----------
FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libicu-dev \
    libonig-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    zip \
    bcmath \
    intl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Habilita mod_rewrite
RUN a2enmod rewrite

# Ajusta DocumentRoot para Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

# Altera o DocumentRoot do Apache dentro da imagem Docker
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Permite .htaccess
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# instalar composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copia apenas arquivos do composer primeiro (melhor cache)
COPY composer.json composer.lock ./

# instalar dependências PHP
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-scripts

# copiar projeto
COPY . .

# Executa scripts do Laravel após copiar tudo
RUN composer dump-autoload --optimize

# copiar assets compilados do stage node
COPY --from=assets /app/public/build ./public/build

# Permissões necessárias
RUN chown -R www-data:www-data \
    storage \
    bootstrap/cache

RUN php artisan storage:link

# Porta do Apache
EXPOSE 80