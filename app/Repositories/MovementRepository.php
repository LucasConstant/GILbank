<?php

namespace App\Repositories;

use App\Contracts\Repositories\MovementRepositoryInterface;
use App\Models\Movement;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class MovementRepository implements MovementRepositoryInterface
{
    public function create(array $data): Movement
    {
        return Movement::query()->create($data);
    }

    public function listByAccountAndPeriod(
        int $accountId,
        CarbonInterface $startDate,
        CarbonInterface $endDate
    ): Collection {
        return Movement::query()
            ->with('destinationAccount.customer')
            ->where('conta_id', $accountId)
            ->whereBetween('created_at', [
                $startDate->copy()->startOfDay(),
                $endDate->copy()->endOfDay(),
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }
}
