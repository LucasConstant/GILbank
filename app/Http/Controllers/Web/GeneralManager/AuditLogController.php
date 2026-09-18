<?php

namespace App\Http\Controllers\Web\GeneralManager;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use OwenIt\Auditing\Models\Audit;

class AuditLogController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogs,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Audit::class);

        $filters = [
            'manager_id' => $request->integer('manager_id') ?: null,
            'event' => $request->string('event')->toString() ?: null,
        ];

        return view('general-manager.audits.index', [
            'audits' => $this->auditLogs->paginate(array_filter($filters)),
            'managers' => $this->auditLogs->accountManagers(),
            'filters' => $filters,
        ]);
    }
}
