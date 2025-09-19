<?php

namespace app\controllers;
use app\services\Loan\LoanServiceInterface;
use app\services\Loan\Models\SubmitLoanRequest as SubmitLoanRequestModel;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

class LoanController extends Controller
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
    public function actionRequests(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $body = json_decode(Yii::$app->request->getRawBody(), true) ?: [];
        $userId = (int)($body['user_id'] ?? 0);
        $amount = (int)($body['amount'] ?? 0);
        $term = (int)($body['term'] ?? 0);

        if ($userId <= 0 || $amount <= 0 || $term <= 0) {
            Yii::$app->response->statusCode = 400;
            return $this->asJson(['result' => false]);
        }

        /** @var LoanServiceInterface $loanService */
        $loanService = Yii::$app->get('loanService');
        $result = $loanService->submitLoanRequest(new SubmitLoanRequestModel($userId, $amount, $term));
        if (!$result->result || $result->id === null) {
            Yii::$app->response->statusCode = 400;
            return $this->asJson(['result' => false]);
        }

        Yii::$app->response->statusCode = 201;
        return $this->asJson(['result' => true, 'id' => (int)$result->id]);
    }
}


