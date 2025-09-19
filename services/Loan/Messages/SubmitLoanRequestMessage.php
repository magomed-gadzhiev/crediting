<?php

namespace app\services\Loan\Messages;

use app\services\Messaging\QueueMessageInterface;
use yii\base\Model;

class SubmitLoanRequestMessage extends Model implements QueueMessageInterface
{
    public int $userId;
    public int $amount;
    public int $term;

    public function __construct(int $userId, int $amount, int $term)
    {
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

    public function toArray(array $fields = [], array $expand = [], $recursive = true): array
    {
        return [
            'user_id' => $this->userId,
            'amount' => $this->amount,
            'term' => $this->term,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self((int)($data['user_id'] ?? 0), (int)($data['amount'] ?? 0), (int)($data['term'] ?? 0));
    }
}


