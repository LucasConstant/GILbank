<?php

namespace App\Repositories;

use App\Contracts\Repositories\AuditRepositoryInterface;
use App\Models\Account;
use App\Models\LimitRequest;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use OwenIt\Auditing\Models\Audit;

class AuditRepository implements AuditRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Audit::query()->with('user')->latest();

        if (! empty($filters['manager_id'])) {
            $managerId = (int) $filters['manager_id'];

            $query->where(function ($builder) use ($managerId): void {
                $builder
                    ->where(function ($inner) use ($managerId): void {
                        $inner->where('user_type', User::class)
                            ->where('user_id', $managerId);
                    })
                    ->orWhere(function ($inner) use ($managerId): void {
                        $inner->where('auditable_type', Account::class)
                            ->whereIn('auditable_id', function ($sub) use ($managerId): void {
                                $sub->select('id')
                                    ->from('contas')
                                    ->where('gerente_id', $managerId);
                            });
                    })
                    ->orWhere(function ($inner) use ($managerId): void {
                        $inner->where('auditable_type', LimitRequest::class)
                            ->whereIn('auditable_id', function ($sub) use ($managerId): void {
                                $sub->select('id')
                                    ->from('solicitacoes_limite')
                                    ->where('gerente_id', $managerId);
                            });
                    });
            });
        }

        if (! empty($filters['event'])) {
            $query->where('event', $filters['event']);
        }

        if (! empty($filters['auditable_type'])) {
            $query->where('auditable_type', $filters['auditable_type']);
        }

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?Audit
    {
        return Audit::query()->with('user')->find($id);
    }
}
