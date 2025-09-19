<?php

namespace app\services\Loan\Models;

use yii\base\Model;

class SubmitLoanRequest extends Model
{
    public int $userId;
    public int $amount;
    public int $term;

    public function __construct(int $userId, int $amount, int $term)
    {
        parent::__construct();
        $this->userId = $userId;
        $this->amount = $amount;
        $this->term = $term;
    }

    public function rules(): array
    {
        return [
            [['userId', 'amount', 'term'], 'required'],
            [['userId', 'amount', 'term'], 'integer', 'min' => 1],
        ];
    }
}


