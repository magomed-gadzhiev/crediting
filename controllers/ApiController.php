<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;

abstract class ApiController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $allowedOrigin = getenv('CORS_ALLOWED_ORIGIN') ?: 'http://localhost';
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => [$allowedOrigin],
                'Access-Control-Request-Method' => ['GET', 'POST', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['Content-Type'],
                'Access-Control-Allow-Credentials' => false,
                'Access-Control-Max-Age' => 600,
                'Access-Control-Expose-Headers' => [],
            ],
        ];

        return $behaviors;
    }

    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }
}



