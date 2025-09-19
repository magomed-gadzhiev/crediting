<?php

use app\models\LoanRequest;
use app\services\Loan\LoanServiceInterface;
use app\services\Loan\Models\ApplyDecision;

class DecisionMutexCest
{
    public function _before(UnitTester $I): void
    {
        \Yii::$app->db->createCommand('TRUNCATE TABLE loan_requests RESTART IDENTITY')->execute();
        putenv('APPROVAL_PROBABILITY=100');
    }

    public function ensureOnlyOneApprovedPerUser(UnitTester $I): void
    {
        // prepare two pending requests for the same user
        \Yii::$app->db->createCommand()->batchInsert('loan_requests', [
            'user_id', 'amount', 'term', 'status', 'created_at', 'updated_at'
        ], [
            [99, 1000, 10, LoanRequest::STATUS_PENDING, time(), time()],
            [99, 1500, 20, LoanRequest::STATUS_PENDING, time(), time()],
        ])->execute();

        /** @var LoanServiceInterface $loanService */
        $loanService = \Yii::$app->get('loanService');

        $first = new ApplyDecision();
        $first->requestId = 1;
        $first->userId = 99;
        $first->decision = LoanRequest::STATUS_APPROVED;

        $second = new ApplyDecision();
        $second->requestId = 2;
        $second->userId = 99;
        $second->decision = LoanRequest::STATUS_APPROVED;

        // simulate near-concurrent execution by running back-to-back; mutex + SERIALIZABLE ensures correctness
        $loanService->applyDecision($first);
        $loanService->applyDecision($second);

        $approvedCount = (int) (new \yii\db\Query())
            ->from(LoanRequest::tableName())
            ->where(['user_id' => 99, 'status' => LoanRequest::STATUS_APPROVED])
            ->count();
        $declinedCount = (int) (new \yii\db\Query())
            ->from(LoanRequest::tableName())
            ->where(['user_id' => 99, 'status' => LoanRequest::STATUS_DECLINED])
            ->count();

        $I->assertSame(1, $approvedCount, 'Only one request must be approved');
        $I->assertSame(1, $declinedCount, 'The other request must be declined');
    }
}
