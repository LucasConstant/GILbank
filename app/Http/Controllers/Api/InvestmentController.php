<?php

namespace App\Http\Controllers\Api;

use App\Enums\InvestmentType;
use App\Exceptions\BankingException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\InvestmentOperationRequest;
use App\Http\Resources\AccountResource;
use App\Http\Resources\InvestmentResource;
use App\Http\Resources\MovementResource;
use App\Services\CustomerAuthService;
use App\Services\InvestmentService;
use Illuminate\Http\JsonResponse;

class InvestmentController extends Controller
{
    public function __construct(
        private readonly CustomerAuthService $auth,
        private readonly InvestmentService $investments,
    ) {}

    public function apply(InvestmentOperationRequest $request): JsonResponse
    {
        $account = $this->auth->accountFor($request->user());
        $this->authorize('invest', $account);

        try {
            $result = $this->investments->apply(
                $account,
                InvestmentType::from($request->validated('tipo')),
                $request->validated('valor')
            );
        } catch (BankingException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return $this->respond($result);
    }

    public function redeem(InvestmentOperationRequest $request): JsonResponse
    {
        $account = $this->auth->accountFor($request->user());
        $this->authorize('redeem', $account);

        try {
            $result = $this->investments->redeem(
                $account,
                InvestmentType::from($request->validated('tipo')),
                $request->validated('valor')
            );
        } catch (BankingException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return $this->respond($result);
    }

    /**
     * @param  array{account: \App\Models\Account, investment: \App\Models\Investment, movement: \App\Models\Movement}  $result
     */
    private function respond(array $result): JsonResponse
    {
        return response()->json([
            'message' => 'Operação realizada com sucesso.',
            'account' => new AccountResource($result['account']->load('investments')),
            'investment' => new InvestmentResource($result['investment']),
            'movement' => new MovementResource($result['movement']),
        ], 201);
    }
}
