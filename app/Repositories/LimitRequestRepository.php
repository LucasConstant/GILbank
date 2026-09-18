<?php

namespace App\Repositories;

use App\Contracts\Repositories\LimitRequestRepositoryInterface;
use App\Enums\LimitRequestStatus;
use App\Models\LimitRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LimitRequestRepository implements LimitRequestRepositoryInterface
{
    public function findById(int $id): ?LimitRequest
    {
        return LimitRequest::query()
            ->with(['account.customer', 'manager', 'reviewer'])
            ->find($id);
    }

    public function listByStatus(LimitRequestStatus $status): Collection
    {
        return LimitRequest::query()
            ->with(['account.customer', 'manager'])
            ->where('status', $status)
            ->latest()
            ->get();
    }

    public function paginateByStatus(?LimitRequestStatus $status = null, int $perPage = 15): LengthAwarePaginator
    {
        return LimitRequest::query()
            ->with(['account.customer', 'manager', 'reviewer'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate($perPage);
    }

    public function paginateByManager(int $managerId, int $perPage = 15): LengthAwarePaginator
    {
        return LimitRequest::query()
            ->with(['account.customer', 'reviewer'])
            ->where('gerente_id', $managerId)
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): LimitRequest
    {
        return LimitRequest::query()->create($data);
    }

    public function update(LimitRequest $limitRequest, array $data): LimitRequest
    {
        $limitRequest->fill($data);
        $limitRequest->save();

        return $limitRequest->refresh();
    }
}
