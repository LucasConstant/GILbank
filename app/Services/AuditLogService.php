<?php

namespace App\Services;

use App\Contracts\Repositories\AuditRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Enums\UserRole;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AuditLogService
{
    public function __construct(
        private readonly AuditRepositoryInterface $audits,
        private readonly UserRepositoryInterface $users,
    ) {}

    /**
     * @param  array{manager_id?: int|null, event?: string|null, auditable_type?: string|null}  $filters
     */
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->audits->paginate($filters, $perPage);
    }

    /**
     * @return Collection<int, \App\Models\User>
     */
    public function accountManagers(): Collection
    {
        return $this->users->listByRole(UserRole::AccountManager);
    }
}
