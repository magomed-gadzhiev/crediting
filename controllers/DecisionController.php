<?php

namespace app\controllers;

use app\models\LoanRequest;
use app\services\Decision\Messages\ProcessRequestMessage;
use app\services\Messaging\AmqpFactory;
use app\services\Messaging\AmqpFactoryInterface;
use app\services\Messaging\MessageEnvelope;
use PhpAmqpLib\Message\AMQPMessage;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

class DecisionController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'processor' => ['get'],
                ],
            ],
        ];
    }

    // GET /processor?delay=5
    public function actionProcessor(int $delay = 5): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $pending = LoanRequest::find()->where(['status' => LoanRequest::STATUS_PENDING])->all();
        if ($pending) {
            $connection = AmqpFactory::createConnection();
            $channel = $connection->channel();
            try {
                AmqpFactory::declareTopology($channel);
                foreach ($pending as $loan) {
                    $message = new ProcessRequestMessage((int)$loan->id, (int)$loan->user_id, (int)$delay);
                    if (!$message->validate()) {
                        continue;
                    }
                    $envelope = new MessageEnvelope(
                        AmqpFactoryInterface::RK_CREDITING_PROCESS_REQUEST,
                        $message->toArray()
                    );
                    $payload = json_encode($envelope->toArray(), JSON_UNESCAPED_UNICODE);
                    $msg = new AMQPMessage($payload, [
                        'content_type' => 'application/json',
                        'delivery_mode' => 2,
                    ]);
                    $channel->basic_publish(
                        $msg,
                        AmqpFactoryInterface::EXCHANGE_DECISION_COMMANDS,
                        AmqpFactoryInterface::RK_CREDITING_PROCESS_REQUEST
                    );
                }
            } finally {
                try { $channel->close(); } catch (\Throwable $e) {}
                try { $connection->close(); } catch (\Throwable $e) {}
            }
        }

        return $this->asJson(['result' => true]);
    }
}


