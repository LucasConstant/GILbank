<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Account */
class AccountResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'saldo' => $this->saldo,
            'limite' => $this->limite,
            'saldo_disponivel' => $this->availableBalance(),
            'bloqueada' => $this->isBlocked(),
            'investments' => InvestmentResource::collection($this->whenLoaded('investments')),
            'customer' => new UserResource($this->whenLoaded('customer')),
        ];
    }
}
