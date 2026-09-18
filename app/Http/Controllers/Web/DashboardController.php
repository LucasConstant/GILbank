<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->isGeneralManager()) {
            return redirect()->route('general-manager.managers.index');
        }

        if ($user->isAccountManager()) {
            return redirect()->route('account-manager.accounts.index');
        }

        abort(403, 'Clientes devem utilizar o aplicativo SPA.');
    }
}
