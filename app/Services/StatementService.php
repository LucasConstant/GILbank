<?php

namespace App\Services;

use App\Contracts\Repositories\MovementRepositoryInterface;
use App\Models\Account;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class StatementService
{
    public function __construct(
        private readonly MovementRepositoryInterface $movements,
    ) {}

    /**
     * @return Collection<int, \App\Models\Movement>
     */
    public function forAccount(Account $account, string $startDate, string $endDate): Collection
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        if ($start->greaterThan($end)) {
            throw new InvalidArgumentException('A data inicial não pode ser maior que a data final.');
        }

        return $this->movements->listByAccountAndPeriod($account->id, $start, $end);
    }
}
