<?php

class ProcessorCest
{
    public function _before(FunctionalTester $I): void
    {
        // make sure there is at least one pending request to trigger publishing
        \Yii::$app->db->createCommand('TRUNCATE TABLE loan_requests RESTART IDENTITY')->execute();
        \Yii::$app->db->createCommand()->insert('loan_requests', [
            'user_id' => 77,
            'amount' => 3000,
            'term' => 30,
            'status' => 'pending',
            'created_at' => time(),
            'updated_at' => time(),
        ])->execute();
    }

    public function triggerProcessor(FunctionalTester $I): void
    {
        $I->sendGet('/processor', ['delay' => 1]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['result' => true]);
    }
}
