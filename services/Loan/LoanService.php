<?php

namespace app\services\Loan;

use app\models\LoanRequest;
use app\services\Loan\Models\ApplyDecision;
use app\services\Loan\Models\ApplyDecisionResult;
use app\services\Loan\Models\SubmitLoanRequest;
use app\services\Loan\Models\SubmitLoanRequestResult;
use Yii;
use yii\db\Transaction;

class LoanService implements LoanServiceInterface
{
    public function submitLoanRequest(SubmitLoanRequest $request): SubmitLoanRequestResult
    {
        $result = new SubmitLoanRequestResult(false, null, null);

        $tx = Yii::$app->db->beginTransaction(Transaction::SERIALIZABLE);
        try {
            // Business constraint: a user must not have approved requests
            $hasApproved = (new \yii\db\Query())
                ->from(LoanRequest::tableName())
                ->where(['user_id' => (int)$request->userId, 'status' => LoanRequest::STATUS_APPROVED])
                ->exists();
            if ($hasApproved) {
                $tx->rollBack();

                // Return unsuccessful result without creating a record
                $result->result = false;
                $result->id = null;
                $result->error = 'User already has approved request';
                return $result;
            }

            $loan = new LoanRequest();
            $loan->user_id = $request->userId;
            $loan->amount = $request->amount;
            $loan->term = $request->term;
            $loan->status = LoanRequest::STATUS_PENDING;
            $loan->created_at = time();
            $loan->updated_at = time();
            if (!$loan->save()) {
                throw new \RuntimeException('Failed to save');
            }

            $tx->commit();
            $result->result = true;
            $result->id = (int)$loan->id;
        } catch (\Throwable $e) {
            if ($tx->isActive) {
                $tx->rollBack();
            }
            $result->error = $e->getMessage();
        }

        return $result;
    }

    public function applyDecision(ApplyDecision $command): ApplyDecisionResult
    {
        $result = new ApplyDecisionResult(false);
        $userId = $command->userId;
        $lockKey = 'loan.applyDecision.user.' . (string)$userId;
        $lockAcquired = false;

        try {
            if (Yii::$app->has('mutex')) {
                // wait up to 30 seconds to acquire lock to avoid dropping messages under contention
                $lockAcquired = Yii::$app->mutex->acquire($lockKey, 30);
                if (!$lockAcquired) {
                    return $result;
                }
            }

            $tx = Yii::$app->db->beginTransaction(Transaction::SERIALIZABLE);
            try {
                $requestId = $command->requestId;
                $decision = $command->decision;

                $loan = LoanRequest::findOne($requestId);
                if (!$loan) {
                    throw new \RuntimeException('Loan not found');
                }

                if (in_array($loan->status, [LoanRequest::STATUS_APPROVED, LoanRequest::STATUS_DECLINED], true)) {
                    $tx->commit();
                    $result->result = true;
                    return $result;
                }

                if ($decision === LoanRequest::STATUS_APPROVED) {
                    $hasApproved = (new \yii\db\Query())
                        ->from(LoanRequest::tableName())
                        ->where(['user_id' => $userId, 'status' => LoanRequest::STATUS_APPROVED])
                        ->exists();
                    if ($hasApproved) {
                        $loan->status = LoanRequest::STATUS_DECLINED;
                    } else {
                        $loan->status = LoanRequest::STATUS_APPROVED;
                    }
                } else {
                    $loan->status = LoanRequest::STATUS_DECLINED;
                }
                $loan->updated_at = time();
                if (!$loan->save(false)) {
                    throw new \RuntimeException('Failed to update');
                }
                $tx->commit();
                $result->result = true;
                return $result;
            } catch (\Throwable $e) {
                if (isset($tx) && $tx->isActive) {
                    $tx->rollBack();
                }
                return $result;
            }
        } finally {
            if ($lockAcquired && Yii::$app->has('mutex')) {
                Yii::$app->mutex->release($lockKey);
            }
        }
    }
}


