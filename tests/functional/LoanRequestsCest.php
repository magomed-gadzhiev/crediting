<?php

class LoanRequestsCest
{
    public function _before(FunctionalTester $I): void
    {
        // ensure database is clean between tests
        \Yii::$app->db->createCommand('TRUNCATE TABLE loan_requests RESTART IDENTITY')->execute();
        // send JSON bodies by default
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/json');
    }

    public function createRequestSuccessfully(FunctionalTester $I): void
    {
        $I->sendPost('/requests', [
            'user_id' => 1,
            'amount' => 3000,
            'term' => 30,
        ]);
        $I->seeResponseCodeIs(201);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['result' => true]);
        $I->seeResponseMatchesJsonType([
            'result' => 'boolean',
            'id' => 'integer',
        ]);
    }

    public function createRequestValidationFails(FunctionalTester $I): void
    {
        $I->sendPost('/requests', [
            'user_id' => 0,
            'amount' => 3000,
            'term' => 30,
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['result' => false]);
    }

    public function createRequestWhenApprovedExistsShouldFail(FunctionalTester $I): void
    {
        // insert approved request for user 10
        \Yii::$app->db->createCommand()->insert('loan_requests', [
            'user_id' => 10,
            'amount' => 1000,
            'term' => 10,
            'status' => 'approved',
            'created_at' => time(),
            'updated_at' => time(),
        ])->execute();

        $I->sendPost('/requests', [
            'user_id' => 10,
            'amount' => 2000,
            'term' => 20,
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['result' => false]);
    }
}
