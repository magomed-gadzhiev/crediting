<?php

namespace app\services\Loan\Models;

use yii\base\Model;

class ApplyDecision extends Model
{
    public int $requestId;
    public int $userId;
    public string $decision;

    public function rules(): array
    {
        return [
            [['requestId', 'userId', 'decision'], 'required'],
            [['requestId', 'userId'], 'integer', 'min' => 1],
            ['decision', 'in', 'range' => ['approved', 'declined']],
        ];
    }
}


