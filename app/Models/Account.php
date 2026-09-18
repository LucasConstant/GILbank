<?php

namespace App\Models;

use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

#[Fillable(['user_id', 'gerente_id', 'saldo', 'limite', 'bloqueada'])]
class Account extends Model implements Auditable
{
    /** @use HasFactory<AccountFactory> */
    use AuditableTrait, HasFactory;

    protected $table = 'contas';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'saldo' => 'decimal:2',
            'limite' => 'decimal:2',
            'bloqueada' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gerente_id');
    }

    public function limitRequests(): HasMany
    {
        return $this->hasMany(LimitRequest::class, 'conta_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class, 'conta_id');
    }

    public function investments(): HasMany
    {
        return $this->hasMany(Investment::class, 'conta_id');
    }

    public function availableBalance(): string
    {
        return bcadd((string) $this->saldo, (string) $this->limite, 2);
    }

    public function isBlocked(): bool
    {
        return $this->bloqueada === true;
    }
}
