<?php

namespace app\services\Loan\Models;

use yii\base\Model;

class ApplyDecisionResult extends Model
{
    public bool $result;

    public function __construct(bool $result)
    {
        parent::__construct();
        $this->result = $result;
    }

    public function rules(): array
    {
        return [
            [['result'], 'required'],
            [['result'], 'boolean'],
        ];
    }
}


