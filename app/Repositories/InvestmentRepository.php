<?php

namespace App\Repositories;

use App\Contracts\Repositories\InvestmentRepositoryInterface;
use App\Enums\InvestmentType;
use App\Models\Investment;
use Illuminate\Support\Collection;

class InvestmentRepository implements InvestmentRepositoryInterface
{
    public function listByAccount(int $accountId): Collection
    {
        return Investment::query()
            ->where('conta_id', $accountId)
            ->orderBy('tipo')
            ->get();
    }

    public function findByAccountAndType(int $accountId, InvestmentType $type): ?Investment
    {
        return Investment::query()
            ->where('conta_id', $accountId)
            ->where('tipo', $type)
            ->first();
    }

    public function findByAccountAndTypeForUpdate(int $accountId, InvestmentType $type): ?Investment
    {
        return Investment::query()
            ->where('conta_id', $accountId)
            ->where('tipo', $type)
            ->lockForUpdate()
            ->first();
    }

    public function create(array $data): Investment
    {
        return Investment::query()->create($data);
    }

    public function update(Investment $investment, array $data): Investment
    {
        $investment->fill($data);
        $investment->save();

        return $investment->refresh();
    }
}
