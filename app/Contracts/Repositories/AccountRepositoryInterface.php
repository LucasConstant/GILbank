<?php

namespace App\Contracts\Repositories;

use App\Models\Account;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface AccountRepositoryInterface
{
    public function findById(int $id): ?Account;

    public function findByIdForUpdate(int $id): ?Account;

    public function findByCustomerId(int $userId): ?Account;

    /**
     * @return Collection<int, Account>
     */
    public function listManagedBy(int $managerId): Collection;

    public function paginateManagedBy(int $managerId, int $perPage = 15): LengthAwarePaginator;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Account;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Account $account, array $data): Account;

    public function delete(Account $account): void;
}
