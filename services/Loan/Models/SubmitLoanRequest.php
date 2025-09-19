<?php

namespace app\services\Loan\Models;

use yii\base\Model;

class SubmitLoanRequest extends Model
{
    public ?int $userId = null;
    public ?int $amount = null;
    public ?int $term = null;

    public function rules(): array
    {
        return [
            [['userId', 'amount', 'term'], 'required'],
            [['userId', 'amount', 'term'], 'integer', 'min' => 1],
        ];
    }
}


