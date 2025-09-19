<?php

namespace app\commands;

use app\services\Loan\LoanServiceInterface;
use app\services\Loan\Models\SubmitLoanRequest;
use app\services\Loan\Models\ApplyDecision;
use app\services\Loan\Messages\SubmitLoanRequestMessage;
use app\services\Loan\Messages\ApplyDecisionMessage;
use app\services\Messaging\MessageEnvelope;
use app\services\Messaging\AmqpFactoryInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Yii;

class LoanController extends CommandController
{
    // RPC server: SubmitLoanRequest
    public function actionRpcSubmit(): int
    {
        $channel = $this->channel;

        $callback = function (AMQPMessage $req) use ($channel) {
            $replyTo = $req->get('reply_to');
            $correlationId = $req->get('correlation_id');
            $payload = json_decode($req->getBody(), true) ?: [];
            $envelope = MessageEnvelope::fromArray($payload);
            if (!$envelope->validate()) {
                $result = [ 'result' => false, 'data' => null, 'error' => 'Envelope validation failed', 'correlationId' => $correlationId ];
                $msg = new AMQPMessage(json_encode($result), [
                    'correlation_id' => $correlationId,
                    'content_type' => 'application/json',
                    'delivery_mode' => 2,
                ]);
                $channel->basic_publish($msg, '', $replyTo);
                $req->ack();
                return;
            }

            $result = [ 'result' => false, 'data' => null, 'error' => null, 'correlationId' => $correlationId ];

            $message = SubmitLoanRequestMessage::fromArray($envelope->payload ?? []);
            if (!$message->validate()) {
                $result['error'] = 'Validation failed';
                $msg = new AMQPMessage(json_encode($result), [
                    'correlation_id' => $correlationId,
                    'content_type' => 'application/json',
                    'delivery_mode' => 2,
                ]);
                $channel->basic_publish($msg, '', $replyTo);
                $req->ack();
                return;
            }
            /** @var LoanServiceInterface $loanService */
            $loanService = Yii::$app->get('loanService');

            $submitLoanRequest = new SubmitLoanRequest();
            $submitLoanRequest->userId = $message->userId;
            $submitLoanRequest->amount = $message->amount;
            $submitLoanRequest->term = $message->term;
            $serviceResult = $loanService->submitLoanRequest($submitLoanRequest);
            $result['result'] = (bool)$serviceResult->result;
            if ($result['result'] && $serviceResult->id !== null) {
                $result['data'] = ['id' => $serviceResult->id];
            } else {
                $result['error'] = $serviceResult->error ?? 'Unknown error';
            }

            $msg = new AMQPMessage(json_encode($result), [
                'correlation_id' => $correlationId,
                'content_type' => 'application/json',
                'delivery_mode' => 2,
            ]);
            $channel->basic_publish($msg, '', $replyTo);
            $req->ack();
        };

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume(AmqpFactoryInterface::QUEUE_APPLICATION_COMMAND_SYNC, '', false, false, false, false, $callback);

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        return 0;
    }

    // Async consumer: ApplyDecision
    public function actionConsumeApplyDecision(): int
    {
        $channel = $this->channel;

        $callback = function (AMQPMessage $req) {
            $payload = json_decode($req->getBody(), true) ?: [];
            $envelope = MessageEnvelope::fromArray($payload);
            if (!$envelope->validate()) {
                $req->nack(false, false);
                return;
            }
            $message = ApplyDecisionMessage::fromArray($envelope->payload ?? []);
            if (!$message->validate()) {
                $req->nack(false, false);
                return;
            }
            /** @var LoanServiceInterface $loanService */
            $loanService = Yii::$app->get('loanService');

            $applyDecisionModel = new ApplyDecision();
            $applyDecisionModel->requestId = $message->requestId;
            $applyDecisionModel->userId = $message->userId;
            $applyDecisionModel->decision = $message->decision;

            $ok = $loanService->applyDecision($applyDecisionModel);
            if ($ok->result) {
                $req->ack();
            } else {
                $req->nack(false, false);
            }
        };

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume(AmqpFactoryInterface::QUEUE_APPLICATION_COMMAND_ASYNC, '', false, false, false, false, $callback);

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        return 0;
    }
}


