<?php

namespace app\services\Decision;

use app\services\Decision\Models\ProcessRequest;
use app\services\Decision\Models\StartProcessing;
use app\services\Decision\Models\StartProcessingResult;

interface DecisionServiceInterface
{
    /**
     * Processes a single loan request decision with delay and publishes result.
     */
    public function processRequest(ProcessRequest $request): void;

    /**
     * Starts processing of all pending loan requests with the given delay.
     */
    public function startProcessing(StartProcessing $command): StartProcessingResult;
}


