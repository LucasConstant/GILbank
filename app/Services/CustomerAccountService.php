<?php

namespace App\Services;

use App\Contracts\Repositories\AccountRepositoryInterface;
use App\Contracts\Repositories\InvestmentRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Enums\InvestmentType;
use App\Enums\UserRole;
use App\Events\AccountBlockStatusChanged;
use App\Events\UserCredentialsCreated;
use App\Exceptions\BankingException;
use App\Models\Account;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CustomerAccountService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly AccountRepositoryInterface $accounts,
        private readonly InvestmentRepositoryInterface $investments,
    ) {}

    public function paginateForManager(User $manager, int $perPage = 15): LengthAwarePaginator
    {
        return $this->accounts->paginateManagedBy($manager->id, $perPage);
    }

    public function findManagedOrFail(User $manager, int $accountId): Account
    {
        $account = $this->accounts->findById($accountId);

        if ($account === null || $account->gerente_id !== $manager->id) {
            throw BankingException::accountNotFound();
        }

        return $account;
    }

    /**
     * @param  array{name: string, email: string, password?: string|null, saldo: string|float, limite: string|float}  $data
     */
    public function create(User $manager, array $data): Account
    {
        $plainPassword = $data['password'] ?? Str::password(12);

        [$customer, $account] = DB::transaction(function () use ($manager, $data, $plainPassword): array {
            $customer = $this->users->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($plainPassword),
                'role' => UserRole::Customer,
                'email_verified_at' => now(),
            ]);

            $account = $this->accounts->create([
                'user_id' => $customer->id,
                'gerente_id' => $manager->id,
                'saldo' => $this->normalizeMoney($data['saldo']),
                'limite' => $this->normalizeMoney($data['limite']),
                'bloqueada' => false,
            ]);

            foreach (InvestmentType::cases() as $type) {
                $this->investments->create([
                    'conta_id' => $account->id,
                    'tipo' => $type,
                    'saldo_aplicado' => '0.00',
                ]);
            }

            return [$customer, $account->load(['customer', 'investments'])];
        });

        UserCredentialsCreated::dispatch(
            $customer,
            $plainPassword,
            'Sua conta de Cliente foi criada no GILbank. O número da conta é #'.$account->id.'.'
        );

        return $account;
    }

    /**
     * @param  array{name?: string, email?: string, password?: string|null, saldo?: string|float|null, limite?: string|float|null}  $data
     */
    public function update(User $manager, Account $account, array $data): Account
    {
        if ($account->gerente_id !== $manager->id) {
            throw BankingException::accountNotFound();
        }

        return DB::transaction(function () use ($account, $data): Account {
            $customerPayload = array_filter([
                'name' => $data['name'] ?? null,
                'email' => $data['email'] ?? null,
            ], fn ($value) => $value !== null);

            if (! empty($data['password'])) {
                $customerPayload['password'] = Hash::make($data['password']);
            }

            if ($customerPayload !== []) {
                $this->users->update($account->customer, $customerPayload);
            }

            $accountPayload = [];

            if (array_key_exists('saldo', $data) && $data['saldo'] !== null) {
                $accountPayload['saldo'] = $this->normalizeMoney($data['saldo']);
            }

            if (array_key_exists('limite', $data) && $data['limite'] !== null) {
                $accountPayload['limite'] = $this->normalizeMoney($data['limite']);
            }

            if ($accountPayload !== []) {
                $this->accounts->update($account, $accountPayload);
            }

            return $account->refresh()->load(['customer', 'investments']);
        });
    }

    public function delete(User $manager, Account $account): void
    {
        if ($account->gerente_id !== $manager->id) {
            throw BankingException::accountNotFound();
        }

        DB::transaction(function () use ($account): void {
            $customer = $account->customer;
            $this->accounts->delete($account);
            $this->users->delete($customer);
        });
    }

    public function block(User $manager, Account $account): Account
    {
        return $this->setBlockStatus($manager, $account, true);
    }

    public function unblock(User $manager, Account $account): Account
    {
        return $this->setBlockStatus($manager, $account, false);
    }

    private function setBlockStatus(User $manager, Account $account, bool $blocked): Account
    {
        if ($account->gerente_id !== $manager->id) {
            throw BankingException::accountNotFound();
        }

        if ($account->bloqueada === $blocked) {
            return $account;
        }

        $account = $this->accounts->update($account, ['bloqueada' => $blocked]);

        AccountBlockStatusChanged::dispatch($account, $blocked, $manager);

        return $account;
    }

    private function normalizeMoney(string|float|int $value): string
    {
        $normalized = number_format((float) $value, 2, '.', '');

        if (bccomp($normalized, '0', 2) < 0) {
            throw new InvalidArgumentException('Valores monetários não podem ser negativos.');
        }

        return $normalized;
    }
}
