<?php

namespace app\commands;

use app\services\Messaging\AmqpFactory;
use yii\console\Controller;

abstract class CommandController extends Controller
{
    protected ?\PhpAmqpLib\Connection\AMQPStreamConnection $connection = null;

    protected ?\PhpAmqpLib\Channel\AMQPChannel $channel = null;

    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $this->connection = AmqpFactory::createConnection();
        $this->channel = $this->connection->channel();
        // Ensure topology is declared before consuming
        \app\services\Messaging\AmqpFactory::declareTopology($this->channel);

        return true;
    }

    public function afterAction($action, $result)
    {
        try {
            if ($this->channel) {
                $this->channel->close();
            }
        } catch (\Throwable $e) {
            // ignore close errors
        }
        try {
            if ($this->connection) {
                $this->connection->close();
            }
        } catch (\Throwable $e) {
            // ignore close errors
        }

        return parent::afterAction($action, $result);
    }
}


