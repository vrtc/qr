FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
        libpng-dev \
        libcurl4-openssl-dev \
        zip unzip git \
    && docker-php-ext-install gd curl \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-interaction --prefer-dist --no-dev \
    && chmod -R 775 runtime/ web/assets/

# SQLite db stored in a separate volume-friendly dir
RUN mkdir -p /app/data

EXPOSE 9888

CMD sh -c "php yii migrate --interactive=0 && php yii serve 0.0.0.0 --port=9888"
