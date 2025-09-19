<?php

namespace app\services\Loan\Messages;

use app\services\Messaging\QueueMessageInterface;
use yii\base\Model;

class ApplyDecisionMessage extends Model implements QueueMessageInterface
{
    public int $requestId;
    public int $userId;
    public string $decision;

    public function __construct(int $requestId, int $userId, string $decision)
    {
        parent::__construct();
        $this->requestId = $requestId;
        $this->userId = $userId;
        $this->decision = $decision;
    }

    public function rules(): array
    {
        return [
            [['requestId', 'userId', 'decision'], 'required'],
            [['requestId', 'userId'], 'integer', 'min' => 1],
            ['decision', 'in', 'range' => ['approved', 'declined']],
        ];
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true): array
    {
        return [
            'request_id' => $this->requestId,
            'user_id' => $this->userId,
            'decision' => $this->decision,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self((int)($data['request_id'] ?? 0), (int)($data['user_id'] ?? 0), (string)($data['decision'] ?? ''));
    }
}


