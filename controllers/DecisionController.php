<?php

namespace app\controllers;

use app\services\Decision\DecisionServiceInterface;
use app\services\Decision\Models\StartProcessingRequest;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Response;

class DecisionController extends ApiController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'processor' => ['GET', 'OPTIONS'],
            ],
        ];
        return $behaviors;
    }

    // GET /processor?delay=5
    public function actionProcessor(StartProcessingRequest $command): Response
    {
        $command->load(Yii::$app->request->get(), '');
        // Map `delay` query param to `delaySeconds` if provided per API contract
        if ($command->delaySeconds === null && $command->delay !== null) {
            $command->delaySeconds = (int)$command->delay;
        }
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


