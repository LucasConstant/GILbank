<?php

namespace App\Contracts\Repositories;

use App\Enums\InvestmentType;
use App\Models\Investment;
use Illuminate\Support\Collection;

interface InvestmentRepositoryInterface
{
    /**
     * @return Collection<int, Investment>
     */
    public function listByAccount(int $accountId): Collection;

    public function findByAccountAndType(int $accountId, InvestmentType $type): ?Investment;

    public function findByAccountAndTypeForUpdate(int $accountId, InvestmentType $type): ?Investment;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Investment;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Investment $investment, array $data): Investment;
}
