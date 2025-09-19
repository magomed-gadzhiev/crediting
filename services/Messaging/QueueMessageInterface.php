<?php

namespace app\services\Messaging;

interface QueueMessageInterface
{
    /**
     * Преобразовать модель сообщения в массив для сериализации в JSON.
     */
    public function toArray(array $fields = [], array $expand = [], $recursive = true): array;

    /**
     * Создать модель сообщения из массива (после json_decode).
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data);
}


