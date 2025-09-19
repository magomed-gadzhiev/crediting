<?php

namespace app\services\Messaging;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Channel\AMQPChannel;

class AmqpFactory
{
    private static bool $topologyInitialized = false;

    public static function initializeTopology(): void
    {
        if (self::$topologyInitialized) {
            return;
        }

        $connection = self::createConnection();
        $channel = $connection->channel();
        try {
            self::declareTopology($channel);
            self::$topologyInitialized = true;
        } finally {
            try {
                $channel->close();
            } catch (\Throwable $e) {
                // ignore close errors
            }
            try {
                $connection->close();
            } catch (\Throwable $e) {
                // ignore close errors
            }
        }
    }

    public static function createConnection(): AMQPStreamConnection
    {
        $host = getenv('RABBITMQ_HOST') ?: 'rabbitmq';
        $port = (int)(getenv('RABBITMQ_PORT') ?: 5672);
        $user = getenv('RABBITMQ_USER') ?: 'guest';
        $pass = getenv('RABBITMQ_PASS') ?: 'guest';

        return new AMQPStreamConnection($host, $port, $user, $pass);
    }

    public static function declareTopology(AMQPChannel $channel): void
    {
        $channel->exchange_declare(AmqpFactoryInterface::EXCHANGE_APPLICATION_COMMANDS, 'topic', false, true, false);
        $channel->exchange_declare(AmqpFactoryInterface::EXCHANGE_DECISION_COMMANDS, 'topic', false, true, false);

        $channel->queue_declare(AmqpFactoryInterface::QUEUE_APPLICATION_COMMAND_SYNC, false, true, false, false);
        $channel->queue_declare(AmqpFactoryInterface::QUEUE_APPLICATION_COMMAND_ASYNC, false, true, false, false);
        $channel->queue_declare(AmqpFactoryInterface::QUEUE_DECISION_COMMAND_ASYNC, false, true, false, false);

        $channel->queue_bind(
            AmqpFactoryInterface::QUEUE_APPLICATION_COMMAND_SYNC,
            AmqpFactoryInterface::EXCHANGE_APPLICATION_COMMANDS,
            AmqpFactoryInterface::RK_CREDITING_SUBMIT
        );
        $channel->queue_bind(
            AmqpFactoryInterface::QUEUE_APPLICATION_COMMAND_ASYNC,
            AmqpFactoryInterface::EXCHANGE_APPLICATION_COMMANDS,
            AmqpFactoryInterface::RK_CREDITING_APPLY_DECISION
        );
        $channel->queue_bind(
            AmqpFactoryInterface::QUEUE_DECISION_COMMAND_ASYNC,
            AmqpFactoryInterface::EXCHANGE_DECISION_COMMANDS,
            AmqpFactoryInterface::RK_CREDITING_PROCESS_REQUEST
        );
    }
}


