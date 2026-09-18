<?php

namespace App\Repositories;

use App\Contracts\Repositories\AccountRepositoryInterface;
use App\Models\Account;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AccountRepository implements AccountRepositoryInterface
{
    public function findById(int $id): ?Account
    {
        return Account::query()->with(['customer', 'manager', 'investments'])->find($id);
    }

    public function findByIdForUpdate(int $id): ?Account
    {
        return Account::query()->whereKey($id)->lockForUpdate()->first();
    }

    public function findByCustomerId(int $userId): ?Account
    {
        return Account::query()
            ->with(['customer', 'investments'])
            ->where('user_id', $userId)
            ->first();
    }

    public function listManagedBy(int $managerId): Collection
    {
        return Account::query()
            ->with(['customer', 'investments'])
            ->where('gerente_id', $managerId)
            ->orderBy('id')
            ->get();
    }

    public function paginateManagedBy(int $managerId, int $perPage = 15): LengthAwarePaginator
    {
        return Account::query()
            ->with(['customer'])
            ->where('gerente_id', $managerId)
            ->orderBy('id')
            ->paginate($perPage);
    }

    public function create(array $data): Account
    {
        return Account::query()->create($data);
    }

    public function update(Account $account, array $data): Account
    {
        $account->fill($data);
        $account->save();

        return $account->refresh();
    }

    public function delete(Account $account): void
    {
        $account->delete();
    }
}
