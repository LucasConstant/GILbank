<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements Auditable
{
    /** @use HasFactory<UserFactory> */
    use AuditableTrait, HasApiTokens, HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $auditExclude = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function account(): HasOne
    {
        return $this->hasOne(Account::class);
    }

    public function managedAccounts(): HasMany
    {
        return $this->hasMany(Account::class, 'gerente_id');
    }

    public function limitRequests(): HasMany
    {
        return $this->hasMany(LimitRequest::class, 'gerente_id');
    }

    public function isGeneralManager(): bool
    {
        return $this->role === UserRole::GeneralManager;
    }

    public function isAccountManager(): bool
    {
        return $this->role === UserRole::AccountManager;
    }

    public function isCustomer(): bool
    {
        return $this->role === UserRole::Customer;
    }
}
