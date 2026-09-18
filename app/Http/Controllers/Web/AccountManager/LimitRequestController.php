<?php

namespace App\Http\Controllers\Web\AccountManager;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountManager\StoreLimitRequestRequest;
use App\Models\Account;
use App\Services\LimitRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LimitRequestController extends Controller
{
    public function __construct(
        private readonly LimitRequestService $limitRequests,
    ) {}

    public function index(Request $request): View
    {
        return view('account-manager.limit-requests.index', [
            'limitRequests' => $this->limitRequests->paginateForManager($request->user()),
        ]);
    }

    public function store(StoreLimitRequestRequest $request, Account $account): RedirectResponse
    {
        $this->limitRequests->create(
            $request->user(),
            $account,
            $request->validated('limite_solicitado')
        );

        return back()->with('status', 'Solicitação de aumento de limite enviada.');
    }
}
