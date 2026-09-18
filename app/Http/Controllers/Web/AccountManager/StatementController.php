<?php

namespace App\Http\Controllers\Web\AccountManager;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountManager\StatementFilterRequest;
use App\Models\Account;
use App\Services\StatementService;
use Illuminate\View\View;

class StatementController extends Controller
{
    public function __construct(
        private readonly StatementService $statements,
    ) {}

    public function show(StatementFilterRequest $request, Account $account): View
    {
        $startDate = $request->validated('start_date');
        $endDate = $request->validated('end_date');

        return view('account-manager.accounts.statement', [
            'account' => $account->load('customer'),
            'movements' => $this->statements->forAccount($account, $startDate, $endDate),
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }
}
