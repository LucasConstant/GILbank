<?php

namespace App\Http\Controllers\Web\AccountManager;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountManager\StoreCustomerAccountRequest;
use App\Http\Requests\AccountManager\UpdateCustomerAccountRequest;
use App\Models\Account;
use App\Services\CustomerAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __construct(
        private readonly CustomerAccountService $customerAccounts,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Account::class);

        return view('account-manager.accounts.index', [
            'accounts' => $this->customerAccounts->paginateForManager($request->user()),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Account::class);

        return view('account-manager.accounts.create');
    }

    public function store(StoreCustomerAccountRequest $request): RedirectResponse
    {
        $this->customerAccounts->create($request->user(), $request->validated());

        return redirect()
            ->route('account-manager.accounts.index')
            ->with('status', 'Conta do cliente criada. Credenciais enviadas por e-mail.');
    }

    public function show(Request $request, Account $account): View
    {
        $this->authorize('view', $account);
        $account = $this->customerAccounts->findManagedOrFail($request->user(), $account->id);
        $account->load(['customer', 'investments', 'limitRequests' => fn ($query) => $query->latest()]);

        return view('account-manager.accounts.show', compact('account'));
    }

    public function edit(Request $request, Account $account): View
    {
        $this->authorize('update', $account);
        $account = $this->customerAccounts->findManagedOrFail($request->user(), $account->id);

        return view('account-manager.accounts.edit', compact('account'));
    }

    public function update(UpdateCustomerAccountRequest $request, Account $account): RedirectResponse
    {
        $this->customerAccounts->update($request->user(), $account, $request->validated());

        return redirect()
            ->route('account-manager.accounts.index')
            ->with('status', 'Conta do cliente atualizada.');
    }

    public function destroy(Request $request, Account $account): RedirectResponse
    {
        $this->authorize('delete', $account);
        $this->customerAccounts->delete($request->user(), $account);

        return redirect()
            ->route('account-manager.accounts.index')
            ->with('status', 'Conta do cliente removida.');
    }

    public function block(Request $request, Account $account): RedirectResponse
    {
        $this->authorize('block', $account);
        $this->customerAccounts->block($request->user(), $account);

        return back()->with('status', 'Conta bloqueada por suspeita de fraude.');
    }

    public function unblock(Request $request, Account $account): RedirectResponse
    {
        $this->authorize('unblock', $account);
        $this->customerAccounts->unblock($request->user(), $account);

        return back()->with('status', 'Conta desbloqueada.');
    }
}
