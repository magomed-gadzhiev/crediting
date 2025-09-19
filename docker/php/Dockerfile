FROM php:7.4-fpm-alpine

RUN apk add --no-cache git bash libpq-dev rabbitmq-c rabbitmq-c-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && apk del rabbitmq-c-dev || true

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/memory-limit.ini

CMD ["php-fpm"]

FROM yiisoftware/yii2-php:7.4-fpm

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends libpq-dev; \
    docker-php-ext-install sockets pdo pdo_pgsql; \
    rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]


