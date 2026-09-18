<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Movement */
class MovementResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tipo' => $this->tipo->value,
            'tipo_label' => $this->tipo->label(),
            'valor' => $this->valor,
            'direcao' => $this->direcao->value,
            'direcao_label' => $this->direcao->label(),
            'is_entrada' => $this->isInbound(),
            'conta_destino_id' => $this->conta_destino_id,
            'aplicacao_tipo' => $this->aplicacao_tipo?->value,
            'aplicacao_tipo_label' => $this->aplicacao_tipo?->label(),
            'descricao' => $this->descricao,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
