FROM php:8.2-fpm

# Установка зависимостей
RUN apt-get update && apt-get install -y \
    apt-utils \
    libpq-dev \
    libpng-dev \
    libzip-dev \
    libmongoc-dev \
    libbson-dev \
    libssl-dev \
    zip unzip \
    git \
    && pecl install mongodb redis \
    && docker-php-ext-enable mongodb redis \
    && docker-php-ext-install pdo_mysql pdo_pgsql bcmath gd zip sockets \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Установка Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm@latest \
    && mkdir -p /home/node/.npm \
    && chown -R 1000:1000 /home/node

# Настройка PHP
COPY ./_docker/app/php.ini /usr/local/etc/php/conf.d/php.ini

# Установка Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- \
    --filename=composer \
    --install-dir=/usr/local/bin

# Рабочая директория
WORKDIR /var/www

# Установка зависимостей Composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Копирование исходного кода
COPY . .

# Настройка прав
RUN mkdir -p storage/app/public tmp/{db,redis,minio,mongo,elasticsearch} \
    && chown -R 1000:1000 /var/www \
    && chmod -R 775 storage tmp \
    && npm config set cache /home/node/.npm --global

# Установка пользователя и установка npm-зависимостей
USER 1000
RUN npm install && npm cache clean --force
