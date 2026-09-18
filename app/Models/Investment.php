<?php

namespace App\Models;

use App\Enums\InvestmentType;
use Database\Factories\InvestmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['conta_id', 'tipo', 'saldo_aplicado'])]
class Investment extends Model
{
    /** @use HasFactory<InvestmentFactory> */
    use HasFactory;

    protected $table = 'aplicacoes';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tipo' => InvestmentType::class,
            'saldo_aplicado' => 'decimal:2',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'conta_id');
    }
}
