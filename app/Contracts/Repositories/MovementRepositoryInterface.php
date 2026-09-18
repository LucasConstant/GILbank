<?php

namespace App\Contracts\Repositories;

use App\Models\Movement;
use Illuminate\Support\Collection;
use Carbon\CarbonInterface;

interface MovementRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Movement;

    /**
     * @return Collection<int, Movement>
     */
    public function listByAccountAndPeriod(
        int $accountId,
        CarbonInterface $startDate,
        CarbonInterface $endDate
    ): Collection;
}
