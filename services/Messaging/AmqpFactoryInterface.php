<?php

namespace app\services\Messaging;

interface AmqpFactoryInterface
{
    // Exchanges
    public const EXCHANGE_APPLICATION_COMMANDS = 'crediting.application';
    public const EXCHANGE_DECISION_COMMANDS = 'crediting.decision';

    // Queues
    public const QUEUE_APPLICATION_COMMAND_SYNC = 'crediting.application.command.sync';
    public const QUEUE_APPLICATION_COMMAND_ASYNC = 'crediting.application.command.async';
    public const QUEUE_DECISION_COMMAND_ASYNC = 'crediting.decision.command.async';

    // Routing keys
    public const RK_CREDITING_SUBMIT = 'crediting.submit';
    public const RK_CREDITING_APPLY_DECISION = 'crediting.applyDecision';
    public const RK_CREDITING_PROCESS_REQUEST = 'crediting.processRequest';
}


