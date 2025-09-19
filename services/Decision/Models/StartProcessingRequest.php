<?php

namespace app\services\Decision\Models;

use yii\base\Model;

class StartProcessingRequest extends Model
{
    public ?int $delaySeconds = null;
    public ?int $delay = null; // alias for request param name

    public function rules(): array
    {
        return [
            [['delaySeconds'], 'required'],
            [['delaySeconds'], 'integer', 'min' => 0],
            [['delay'], 'integer', 'min' => 0],
        ];
    }
}



