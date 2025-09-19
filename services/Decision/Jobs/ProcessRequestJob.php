<?php

namespace app\services\Decision\Jobs;

use app\services\Decision\DecisionServiceInterface;
use app\services\Decision\Models\ProcessRequest as ProcessRequestModel;
use Yii;
use yii\queue\JobInterface;

class ProcessRequestJob implements JobInterface
{
    public int $requestId;
    public int $userId;
    public int $delaySeconds = 0;

    public function __construct(array $config = [])
    {
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }
    }

    public function execute($queue)
    {
        /** @var DecisionServiceInterface $decisionService */
        $decisionService = Yii::$app->get('decisionService');
        $decisionService->processRequest(new ProcessRequestModel($this->requestId, $this->userId, $this->delaySeconds));
    }
}


