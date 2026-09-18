<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccountResource;
use App\Http\Resources\InvestmentResource;
use App\Services\CustomerAuthService;
use App\Services\InvestmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function __construct(
        private readonly CustomerAuthService $auth,
        private readonly InvestmentService $investments,
    ) {}

    public function balance(Request $request): JsonResponse
    {
        $account = $this->auth->accountFor($request->user())->load('investments');

        $this->authorize('view', $account);

        return response()->json([
            'account' => new AccountResource($account),
            'investments' => InvestmentResource::collection($this->investments->listForAccount($account)),
        ]);
    }
}
