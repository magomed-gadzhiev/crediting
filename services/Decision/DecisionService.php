<?php

namespace app\services\Decision;

use app\models\LoanRequest;
use app\services\Decision\Models\ProcessRequest;
use app\services\Loan\Messages\ApplyDecisionMessage;
use app\services\Messaging\AmqpFactory;
use app\services\Messaging\AmqpFactoryInterface;
use app\services\Messaging\MessageEnvelope;
use PhpAmqpLib\Message\AMQPMessage;

class DecisionService implements DecisionServiceInterface
{
    public function processRequest(ProcessRequest $request): void
    {
        $requestId = $request->requestId;
        $userId = $request->userId;
        $delaySeconds = $request->delaySeconds;

        if ($requestId <= 0 || $userId <= 0) {
            return;
        }

        sleep(max(0, $delaySeconds));

        $decision = (mt_rand(1, 100) <= 10) ? LoanRequest::STATUS_APPROVED : LoanRequest::STATUS_DECLINED;

        // publish async apply decision via RabbitMQ
        $connection = AmqpFactory::createConnection();
        $channel = $connection->channel();
        try {
            AmqpFactory::declareTopology($channel);
            $message = new ApplyDecisionMessage((int)$requestId, (int)$userId, (string)$decision);
            if (!$message->validate()) {
                return;
            }
            $envelope = new MessageEnvelope(
                AmqpFactoryInterface::RK_CREDITING_APPLY_DECISION,
                $message->toArray()
            );
            $payload = json_encode($envelope->toArray(), JSON_UNESCAPED_UNICODE);
            $msg = new AMQPMessage($payload, [
                'content_type' => 'application/json',
                'delivery_mode' => 2,
            ]);
            $channel->basic_publish(
                $msg,
                AmqpFactoryInterface::EXCHANGE_APPLICATION_COMMANDS,
                AmqpFactoryInterface::RK_CREDITING_APPLY_DECISION
            );
        } finally {
            try { $channel->close(); } catch (\Throwable $e) {}
            try { $connection->close(); } catch (\Throwable $e) {}
        }
    }
}


