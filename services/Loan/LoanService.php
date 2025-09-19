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
            $userId = $request->userId;
            $amount = $request->amount;
            $term = $request->term;

            if ($userId <= 0 || $amount <= 0 || $term <= 0) {
                throw new \InvalidArgumentException('Validation failed');
            }

            $hasApproved = (new \yii\db\Query())
                ->from(LoanRequest::tableName())
                ->where(['user_id' => $userId, 'status' => LoanRequest::STATUS_APPROVED])
                ->exists();
            if ($hasApproved) {
                throw new \DomainException('User already has approved loan');
            }

            $loan = new LoanRequest();
            $loan->user_id = $userId;
            $loan->amount = $amount;
            $loan->term = $term;
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
        $tx = Yii::$app->db->beginTransaction(Transaction::SERIALIZABLE);
        try {
            $requestId = $command->requestId;
            $userId = $command->userId;
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
            if ($tx->isActive) {
                $tx->rollBack();
            }
            return $result;
        }
    }
}


