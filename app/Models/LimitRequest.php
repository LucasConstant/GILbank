<?php

namespace App\Models;

use App\Enums\LimitRequestStatus;
use Database\Factories\LimitRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

#[Fillable(['conta_id', 'gerente_id', 'limite_solicitado', 'status', 'aprovado_por'])]
class LimitRequest extends Model implements Auditable
{
    /** @use HasFactory<LimitRequestFactory> */
    use AuditableTrait, HasFactory;

    protected $table = 'solicitacoes_limite';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'limite_solicitado' => 'decimal:2',
            'status' => LimitRequestStatus::class,
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'conta_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gerente_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprovado_por');
    }

    public function isPending(): bool
    {
        return $this->status === LimitRequestStatus::Pending;
    }
}
