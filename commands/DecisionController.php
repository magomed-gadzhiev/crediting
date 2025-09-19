<?php

namespace app\commands;

use app\services\Decision\DecisionServiceInterface;
use app\services\Decision\Models\ProcessRequest as ProcessRequestModel;
use app\services\Decision\Messages\ProcessRequestMessage;
use app\services\Messaging\MessageEnvelope;
use app\services\Messaging\AmqpFactoryInterface;
use Yii;
use PhpAmqpLib\Message\AMQPMessage;

class DecisionController extends CommandController
{
    // Consumer for ProcessRequest commands
    public function actionConsume(): int
    {
        $channel = $this->channel;

        $callback = function (AMQPMessage $req) use ($channel) {
            $payload = json_decode($req->getBody(), true) ?: [];
            $envelope = MessageEnvelope::fromArray($payload);
            if (!$envelope->validate()) {
                $req->nack(false, false);
                return;
            }
            $message = ProcessRequestMessage::fromArray($envelope->payload ?? []);
            if (!$message->validate()) {
                $req->nack(false, false);
                return;
            }
            /** @var DecisionServiceInterface $decisionService */
            $decisionService = Yii::$app->get('decisionService');
            $decisionService->processRequest(new ProcessRequestModel($message->requestId, $message->userId, $message->delaySeconds));
            $req->ack();
        };

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume(AmqpFactoryInterface::QUEUE_DECISION_COMMAND_ASYNC, '', false, false, false, false, $callback);

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        return 0;
    }
}


