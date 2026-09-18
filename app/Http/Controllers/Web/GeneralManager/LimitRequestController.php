<?php

namespace App\Http\Controllers\Web\GeneralManager;

use App\Http\Controllers\Controller;
use App\Models\LimitRequest;
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
        $this->authorize('viewAny', LimitRequest::class);

        $pendingOnly = $request->string('status')->toString() !== 'all';

        return view('general-manager.limit-requests.index', [
            'limitRequests' => $pendingOnly
                ? $this->limitRequests->paginatePending()
                : $this->limitRequests->paginateAll(),
            'pendingOnly' => $pendingOnly,
        ]);
    }

    public function approve(Request $request, LimitRequest $limitRequest): RedirectResponse
    {
        $this->authorize('approve', $limitRequest);

        $this->limitRequests->approve($request->user(), $limitRequest);

        return back()->with('status', 'Solicitação de limite aprovada.');
    }

    public function reject(Request $request, LimitRequest $limitRequest): RedirectResponse
    {
        $this->authorize('reject', $limitRequest);

        $this->limitRequests->reject($request->user(), $limitRequest);

        return back()->with('status', 'Solicitação de limite reprovada.');
    }
}
