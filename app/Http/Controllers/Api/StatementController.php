<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\BankingException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StatementRequest;
use App\Http\Resources\MovementResource;
use App\Services\CustomerAuthService;
use App\Services\StatementService;
use Illuminate\Http\JsonResponse;

class StatementController extends Controller
{
    public function __construct(
        private readonly CustomerAuthService $auth,
        private readonly StatementService $statements,
    ) {}

    public function index(StatementRequest $request): JsonResponse
    {
        $account = $this->auth->accountFor($request->user());
        $this->authorize('viewStatement', $account);

        try {
            $movements = $this->statements->forAccount(
                $account,
                $request->validated('start_date'),
                $request->validated('end_date')
            );
        } catch (BankingException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'data' => MovementResource::collection($movements),
        ]);
    }
}
