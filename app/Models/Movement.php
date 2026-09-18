<?php

namespace App\Models;

use App\Enums\InvestmentType;
use App\Enums\MovementDirection;
use App\Enums\MovementType;
use Database\Factories\MovementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'conta_id',
    'tipo',
    'valor',
    'direcao',
    'conta_destino_id',
    'aplicacao_tipo',
    'descricao',
])]
class Movement extends Model
{
    /** @use HasFactory<MovementFactory> */
    use HasFactory;

    protected $table = 'movimentacoes';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'tipo' => MovementType::class,
            'direcao' => MovementDirection::class,
            'aplicacao_tipo' => InvestmentType::class,
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'conta_id');
    }

    public function destinationAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'conta_destino_id');
    }

    public function isInbound(): bool
    {
        return $this->direcao === MovementDirection::Inbound;
    }
}
