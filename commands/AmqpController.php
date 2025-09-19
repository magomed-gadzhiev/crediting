<?php

namespace app\commands;

use app\services\Messaging\AmqpFactory;
use yii\console\Controller;
use yii\console\ExitCode;

class AmqpController extends Controller
{
    public function actionInitTopology(): int
    {
        AmqpFactory::initializeTopology();
        $this->stdout("AMQP topology initialized\n");
        return ExitCode::OK;
    }
}


