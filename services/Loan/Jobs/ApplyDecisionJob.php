<?php

namespace app\services\Loan\Jobs;

use app\services\Loan\LoanServiceInterface;
use app\services\Loan\Models\ApplyDecision as ApplyDecisionModel;
use Yii;
use yii\queue\JobInterface;

class ApplyDecisionJob implements JobInterface
{
    public int $requestId;
    public int $userId;
    public string $decision;

    public function __construct(array $config = [])
    {
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }
    }

    public function execute($queue)
    {
        /** @var LoanServiceInterface $loanService */
        $loanService = Yii::$app->get('loanService');
        $loanService->applyDecision(new ApplyDecisionModel($this->requestId, $this->userId, $this->decision));
    }
}


