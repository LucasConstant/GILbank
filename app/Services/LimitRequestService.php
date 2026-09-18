<?php

namespace App\Services;

use App\Contracts\Repositories\AccountRepositoryInterface;
use App\Contracts\Repositories\LimitRequestRepositoryInterface;
use App\Enums\LimitRequestStatus;
use App\Exceptions\BankingException;
use App\Models\Account;
use App\Models\LimitRequest;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LimitRequestService
{
    public function __construct(
        private readonly LimitRequestRepositoryInterface $limitRequests,
        private readonly AccountRepositoryInterface $accounts,
    ) {}

    public function paginatePending(int $perPage = 15): LengthAwarePaginator
    {
        return $this->limitRequests->paginateByStatus(LimitRequestStatus::Pending, $perPage);
    }

    public function paginateAll(int $perPage = 15): LengthAwarePaginator
    {
        return $this->limitRequests->paginateByStatus(null, $perPage);
    }

    public function paginateForManager(User $manager, int $perPage = 15): LengthAwarePaginator
    {
        return $this->limitRequests->paginateByManager($manager->id, $perPage);
    }

    public function create(User $manager, Account $account, string|float $requestedLimit): LimitRequest
    {
        if ($account->gerente_id !== $manager->id) {
            throw BankingException::accountNotFound();
        }

        $amount = $this->normalizeMoney($requestedLimit);

        if (bccomp($amount, (string) $account->limite, 2) <= 0) {
            throw new InvalidArgumentException('O limite solicitado deve ser maior que o limite atual.');
        }

        return $this->limitRequests->create([
            'conta_id' => $account->id,
            'gerente_id' => $manager->id,
            'limite_solicitado' => $amount,
            'status' => LimitRequestStatus::Pending,
        ]);
    }

    public function approve(User $reviewer, LimitRequest $limitRequest): LimitRequest
    {
        return $this->review($reviewer, $limitRequest, LimitRequestStatus::Approved);
    }

    public function reject(User $reviewer, LimitRequest $limitRequest): LimitRequest
    {
        return $this->review($reviewer, $limitRequest, LimitRequestStatus::Rejected);
    }

    private function review(User $reviewer, LimitRequest $limitRequest, LimitRequestStatus $status): LimitRequest
    {
        if (! $limitRequest->isPending()) {
            throw BankingException::limitRequestNotPending();
        }

        return DB::transaction(function () use ($reviewer, $limitRequest, $status): LimitRequest {
            $updated = $this->limitRequests->update($limitRequest, [
                'status' => $status,
                'aprovado_por' => $reviewer->id,
            ]);

            if ($status === LimitRequestStatus::Approved) {
                $account = $this->accounts->findByIdForUpdate($limitRequest->conta_id);

                if ($account === null) {
                    throw BankingException::accountNotFound();
                }

                $this->accounts->update($account, [
                    'limite' => $limitRequest->limite_solicitado,
                ]);
            }

            return $updated->load(['account.customer', 'manager', 'reviewer']);
        });
    }

    private function normalizeMoney(string|float|int $value): string
    {
        $normalized = number_format((float) $value, 2, '.', '');

        if (bccomp($normalized, '0', 2) <= 0) {
            throw BankingException::invalidAmount();
        }

        return $normalized;
    }
}
