<?php

namespace app\controllers;
use app\services\Loan\LoanServiceInterface;
use app\services\Loan\Models\SubmitLoanRequest as SubmitLoanRequestModel;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

class LoanController extends ApiController
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'requests' => ['post'],
                ],
            ],
        ];
    }

    // POST /requests
    public function actionRequests(SubmitLoanRequestModel $model): Response
    {
        $model->load(Yii::$app->request->post(), '');
        if (!$model->validate()) {
            Yii::$app->response->statusCode = 400;
            return $this->asJson(['result' => false]);
        }

        /** @var LoanServiceInterface $loanService */
        $loanService = Yii::$app->get('loanService');
        $result = $loanService->submitLoanRequest($model);
        if (!$result->result || $result->id === null) {
            Yii::$app->response->statusCode = 400;
            return $this->asJson(['result' => false]);
        }

        Yii::$app->response->statusCode = 201;
        return $this->asJson(['result' => true, 'id' => (int)$result->id]);
    }
}


