<?php

namespace app\services\Decision\Models;

use yii\base\Model;

class ProcessRequest extends Model
{
    public int $requestId;
    public int $userId;
    public int $delaySeconds;

    public function __construct(int $requestId, int $userId, int $delaySeconds)
    {
        parent::__construct();
        $this->requestId = $requestId;
        $this->userId = $userId;
        $this->delaySeconds = $delaySeconds;
    }

    public function rules(): array
    {
        return [
            [['requestId', 'userId', 'delaySeconds'], 'required'],
            [['requestId', 'userId', 'delaySeconds'], 'integer', 'min' => 0],
        ];
    }
}


