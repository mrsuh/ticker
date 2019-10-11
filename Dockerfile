FROM php:7.3-cli

RUN apt-get update && apt-get install -y \
        libzip-dev \
        unzip \
        libmcrypt-dev \
        zlib1g-dev \
        libicu-dev \
    && docker-php-ext-install \
        intl \
        opcache \
        pcntl \
        iconv \
        mbstring \
        zip \
        bcmath \
        pdo_mysql \
        mysqli \
    && docker-php-ext-enable \
        intl \
        opcache \
        pcntl \
        iconv \
        mbstring \
        zip \
        bcmath \
        pdo_mysql \
        mysqli

RUN usermod -u 1000 www-data

COPY ./php.ini /usr/local/etc/php/php.ini

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . /app
COPY ./php.ini /usr/local/etc/php/php.ini

ENV COMPOSER_HOME /composer-home

RUN sh /app/bin/build.sh

ENTRYPOINT ["php", "/app/bin/react-worker.php"]