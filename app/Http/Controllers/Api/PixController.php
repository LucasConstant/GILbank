<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\BankingException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PixTransferRequest;
use App\Http\Resources\AccountResource;
use App\Http\Resources\MovementResource;
use App\Services\CustomerAuthService;
use App\Services\TransferService;
use Illuminate\Http\JsonResponse;

class PixController extends Controller
{
    public function __construct(
        private readonly CustomerAuthService $auth,
        private readonly TransferService $transfers,
    ) {}

    public function store(PixTransferRequest $request): JsonResponse
    {
        $account = $this->auth->accountFor($request->user());
        $this->authorize('transfer', $account);

        try {
            $result = $this->transfers->pix(
                $account,
                (int) $request->validated('conta_destino_id'),
                $request->validated('valor'),
                $request->validated('descricao')
            );
        } catch (BankingException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Pix realizado com sucesso.',
            'account' => new AccountResource($result['origin']->load('investments')),
            'movement' => new MovementResource($result['outbound']),
        ], 201);
    }
}
