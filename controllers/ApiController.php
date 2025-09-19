<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;

abstract class ApiController extends Controller
{
    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }
}



