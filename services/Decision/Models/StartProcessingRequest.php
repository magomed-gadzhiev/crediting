<?php

namespace app\services\Decision\Models;

use yii\base\Model;

class StartProcessingRequest extends Model
{
    public ?int $delay = null;

    public function rules(): array
    {
        return [
            [['delay'], 'required'],
            [['delay'], 'integer', 'min' => 0],
        ];
    }
}



