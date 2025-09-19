<?php

namespace app\services\Loan\Models;

use yii\base\Model;

class SubmitLoanRequestResult extends Model
{
    public bool $result;
    public ?int $id;
    public ?string $error;

    public function __construct(bool $result, ?int $id = null, ?string $error = null)
    {
        parent::__construct();
        $this->result = $result;
        $this->id = $id;
        $this->error = $error;
    }

    public function rules(): array
    {
        return [
            [['result'], 'required'],
            [['result'], 'boolean'],
            [['id'], 'integer'],
            [['error'], 'string'],
        ];
    }
}


