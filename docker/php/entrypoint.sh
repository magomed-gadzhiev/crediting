#!/bin/sh
set -e

# Initialize AMQP topology (idempotent)
export RABBITMQ_HOST=${RABBITMQ_HOST:-rabbitmq}
export RABBITMQ_PORT=${RABBITMQ_PORT:-5672}
php /app/yii amqp/init-topology || true

# Start PHP-FPM
exec php-fpm


