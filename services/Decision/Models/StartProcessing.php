<?php

namespace app\services\Decision\Models;

use yii\base\Model;

class StartProcessing extends Model
{
    public ?int $delaySeconds = null;

    public function rules(): array
    {
        return [
            [['delaySeconds'], 'required'],
            [['delaySeconds'], 'integer', 'min' => 0],
        ];
    }
}



