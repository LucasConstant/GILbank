<?php

namespace App\Http\Controllers\Web\GeneralManager;

use App\Http\Controllers\Controller;
use App\Http\Requests\GeneralManager\StoreAccountManagerRequest;
use App\Http\Requests\GeneralManager\UpdateAccountManagerRequest;
use App\Models\User;
use App\Services\AccountManagerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AccountManagerController extends Controller
{
    public function __construct(
        private readonly AccountManagerService $accountManagers,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        return view('general-manager.managers.index', [
            'managers' => $this->accountManagers->paginate(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('general-manager.managers.create');
    }

    public function store(StoreAccountManagerRequest $request): RedirectResponse
    {
        $this->accountManagers->create($request->validated());

        return redirect()
            ->route('general-manager.managers.index')
            ->with('status', 'Gerente de conta criado. Credenciais enviadas por e-mail.');
    }

    public function edit(User $manager): View
    {
        abort_unless($manager->isAccountManager(), 404);
        $this->authorize('update', $manager);

        return view('general-manager.managers.edit', compact('manager'));
    }

    public function update(UpdateAccountManagerRequest $request, User $manager): RedirectResponse
    {
        abort_unless($manager->isAccountManager(), 404);

        $this->accountManagers->update($manager, $request->validated());

        return redirect()
            ->route('general-manager.managers.index')
            ->with('status', 'Gerente de conta atualizado.');
    }

    public function destroy(User $manager): RedirectResponse
    {
        abort_unless($manager->isAccountManager(), 404);
        $this->authorize('delete', $manager);

        try {
            $this->accountManagers->delete($manager);
        } catch (\InvalidArgumentException $exception) {
            return back()->withErrors(['manager' => $exception->getMessage()]);
        }

        return redirect()
            ->route('general-manager.managers.index')
            ->with('status', 'Gerente de conta removido.');
    }
}
