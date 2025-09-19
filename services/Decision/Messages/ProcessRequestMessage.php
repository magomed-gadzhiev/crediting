<?php

namespace app\services\Decision\Messages;

use app\services\Messaging\QueueMessageInterface;
use yii\base\Model;

class ProcessRequestMessage extends Model implements QueueMessageInterface
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

    public function toArray(array $fields = [], array $expand = [], $recursive = true): array
    {
        return [
            'request_id' => $this->requestId,
            'user_id' => $this->userId,
            'delay' => $this->delaySeconds,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self((int)($data['request_id'] ?? 0), (int)($data['user_id'] ?? 0), (int)($data['delay'] ?? 0));
    }
}


