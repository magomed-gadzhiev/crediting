<?php

namespace app\services\Decision;

use app\services\Decision\Models\ProcessRequest;

interface DecisionServiceInterface
{
    /**
     * Processes a single loan request decision with delay and publishes result.
     */
    public function processRequest(ProcessRequest $request): void;
}


