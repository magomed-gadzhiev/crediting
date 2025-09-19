<?php

namespace app\controllers;

use app\services\Decision\DecisionServiceInterface;
use app\services\Decision\Models\StartProcessing as StartProcessingModel;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

class DecisionController extends ApiController
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
    public function actionProcessor(StartProcessingModel $command): Response
    {
        $command->load(Yii::$app->request->get(), '');
        if (!$command->validate()) {
            Yii::$app->response->statusCode = 400;
            return $this->asJson(['result' => false]);
        }

        /** @var DecisionServiceInterface $decisionService */
        $decisionService = Yii::$app->get('decisionService');
        $result = $decisionService->startProcessing($command);

        return $this->asJson(['result' => (bool)$result->result]);
    }
}


