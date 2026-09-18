<?php

namespace App\Contracts\Repositories;

use App\Enums\LimitRequestStatus;
use App\Models\LimitRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface LimitRequestRepositoryInterface
{
    public function findById(int $id): ?LimitRequest;

    /**
     * @return Collection<int, LimitRequest>
     */
    public function listByStatus(LimitRequestStatus $status): Collection;

    public function paginateByStatus(?LimitRequestStatus $status = null, int $perPage = 15): LengthAwarePaginator;

    public function paginateByManager(int $managerId, int $perPage = 15): LengthAwarePaginator;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): LimitRequest;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(LimitRequest $limitRequest, array $data): LimitRequest;
}
