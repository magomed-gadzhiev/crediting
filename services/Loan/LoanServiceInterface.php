<?php

namespace app\services\Loan;

use app\services\Loan\Models\ApplyDecision;
use app\services\Loan\Models\ApplyDecisionResult;
use app\services\Loan\Models\SubmitLoanRequest;
use app\services\Loan\Models\SubmitLoanRequestResult;

interface LoanServiceInterface
{
    /**
     * Creates a loan request transactionally with business constraints.
     */
    public function submitLoanRequest(SubmitLoanRequest $request): SubmitLoanRequestResult;

    /**
     * Applies decision to a loan request transactionally.
     */
    public function applyDecision(ApplyDecision $command): ApplyDecisionResult;
}


