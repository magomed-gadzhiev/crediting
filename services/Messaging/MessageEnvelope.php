<?php

namespace app\services\Messaging;

use Ramsey\Uuid\Uuid;
use yii\base\Model;

class MessageEnvelope extends Model implements QueueMessageInterface
{
    public string $messageId;
    public string $name;
    /** @var array<string,mixed> */
    public array $payload;
    public ?string $correlationId;

    public function __construct(string $name, array $payload = [], ?string $correlationId = null, ?string $messageId = null)
    {
        $this->name = $name;
        $this->payload = $payload;
        $this->correlationId = $correlationId;
        $this->messageId = $messageId ?: Uuid::uuid4()->toString();
        parent::__construct();
    }

    public function rules(): array
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'min' => 1],
            [['payload'], 'validatePayload'],
            [['correlationId'], 'string'],
            [['correlationId'], 'default', 'value' => null],
            [['messageId'], 'string'],
        ];
    }

    public function validatePayload(string $attribute): void
    {
        if (!is_array($this->$attribute)) {
            $this->addError($attribute, 'Payload must be an array');
        }
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true): array
    {
        return [
            'messageId' => $this->messageId,
            'name' => $this->name,
            'payload' => $this->payload,
            'correlationId' => $this->correlationId,
        ];
    }

    public static function fromArray(array $data): self
    {
        $name = (string)($data['name'] ?? '');
        $payload = (array)($data['payload'] ?? []);
        $correlationId = isset($data['correlationId']) ? (string)$data['correlationId'] : null;
        $messageId = isset($data['messageId']) ? (string)$data['messageId'] : null;
        return new self($name, $payload, $correlationId, $messageId);
    }
}


