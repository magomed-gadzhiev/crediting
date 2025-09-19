<?php

namespace app\controllers;
use app\services\Loan\LoanServiceInterface;
use app\services\Loan\Models\SubmitLoanRequest;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Response;

class LoanController extends ApiController
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'requests' => ['POST', 'OPTIONS'],
            ],
        ];
        return $behaviors;
    }

    // POST /requests
    public function actionRequests(): Response
    {
        $submitLoanRequest = new SubmitLoanRequest();
        $submitLoanRequest->userId = Yii::$app->request->post('user_id');
        $submitLoanRequest->amount = Yii::$app->request->post('amount');
        $submitLoanRequest->term = Yii::$app->request->post('term');

        if (!$submitLoanRequest->validate()) {
            Yii::$app->response->statusCode = 400;
            return $this->asJson(['result' => false]);
        }

        /** @var LoanServiceInterface $loanService */
        $loanService = Yii::$app->get('loanService');
        $result = $loanService->submitLoanRequest($submitLoanRequest);
        if (!$result->result || $result->id === null) {
            Yii::$app->response->statusCode = 400;
            return $this->asJson(['result' => false]);
        }

        Yii::$app->response->statusCode = 201;
        return $this->asJson(['result' => true, 'id' => (int)$result->id]);
    }
}


