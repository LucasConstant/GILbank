<?php

namespace App\Services;

use App\Contracts\Repositories\AccountRepositoryInterface;
use App\Contracts\Repositories\InvestmentRepositoryInterface;
use App\Contracts\Repositories\MovementRepositoryInterface;
use App\Enums\InvestmentType;
use App\Enums\MovementDirection;
use App\Enums\MovementType;
use App\Exceptions\BankingException;
use App\Models\Account;
use App\Models\Investment;
use App\Models\Movement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InvestmentService
{
    public function __construct(
        private readonly AccountRepositoryInterface $accounts,
        private readonly InvestmentRepositoryInterface $investments,
        private readonly MovementRepositoryInterface $movements,
    ) {}

    /**
     * @return Collection<int, Investment>
     */
    public function listForAccount(Account $account): Collection
    {
        return $this->investments->listByAccount($account->id);
    }

    /**
     * @return array{account: Account, investment: Investment, movement: Movement}
     */
    public function apply(Account $account, InvestmentType $type, string|float $amount): array
    {
        if ($account->isBlocked()) {
            throw BankingException::accountBlocked();
        }

        $value = $this->normalizeMoney($amount);

        return DB::transaction(function () use ($account, $type, $value): array {
            $lockedAccount = $this->accounts->findByIdForUpdate($account->id);

            if ($lockedAccount === null) {
                throw BankingException::accountNotFound();
            }

            if ($lockedAccount->isBlocked()) {
                throw BankingException::accountBlocked();
            }

            if (bccomp($lockedAccount->availableBalance(), $value, 2) < 0) {
                throw BankingException::insufficientFunds();
            }

            $investment = $this->investments->findByAccountAndTypeForUpdate($lockedAccount->id, $type);

            if ($investment === null) {
                $this->investments->create([
                    'conta_id' => $lockedAccount->id,
                    'tipo' => $type,
                    'saldo_aplicado' => '0.00',
                ]);

                $investment = $this->investments->findByAccountAndTypeForUpdate($lockedAccount->id, $type);
            }

            if ($investment === null) {
                throw BankingException::investmentNotFound();
            }

            $this->accounts->update($lockedAccount, [
                'saldo' => bcsub((string) $lockedAccount->saldo, $value, 2),
            ]);

            $investment = $this->investments->update($investment, [
                'saldo_aplicado' => bcadd((string) $investment->saldo_aplicado, $value, 2),
            ]);

            $movement = $this->movements->create([
                'conta_id' => $lockedAccount->id,
                'tipo' => MovementType::Application,
                'valor' => $value,
                'direcao' => MovementDirection::Outbound,
                'aplicacao_tipo' => $type,
                'descricao' => 'Aplicação em '.$type->label(),
            ]);

            return [
                'account' => $lockedAccount->refresh(),
                'investment' => $investment,
                'movement' => $movement,
            ];
        });
    }

    /**
     * @return array{account: Account, investment: Investment, movement: Movement}
     */
    public function redeem(Account $account, InvestmentType $type, string|float $amount): array
    {
        if ($account->isBlocked()) {
            throw BankingException::accountBlocked();
        }

        $value = $this->normalizeMoney($amount);

        return DB::transaction(function () use ($account, $type, $value): array {
            $lockedAccount = $this->accounts->findByIdForUpdate($account->id);

            if ($lockedAccount === null) {
                throw BankingException::accountNotFound();
            }

            if ($lockedAccount->isBlocked()) {
                throw BankingException::accountBlocked();
            }

            $investment = $this->investments->findByAccountAndTypeForUpdate($lockedAccount->id, $type);

            if ($investment === null) {
                throw BankingException::investmentNotFound();
            }

            if (bccomp((string) $investment->saldo_aplicado, $value, 2) < 0) {
                throw BankingException::insufficientInvestment();
            }

            $this->accounts->update($lockedAccount, [
                'saldo' => bcadd((string) $lockedAccount->saldo, $value, 2),
            ]);

            $investment = $this->investments->update($investment, [
                'saldo_aplicado' => bcsub((string) $investment->saldo_aplicado, $value, 2),
            ]);

            $movement = $this->movements->create([
                'conta_id' => $lockedAccount->id,
                'tipo' => MovementType::Redemption,
                'valor' => $value,
                'direcao' => MovementDirection::Inbound,
                'aplicacao_tipo' => $type,
                'descricao' => 'Resgate de '.$type->label(),
            ]);

            return [
                'account' => $lockedAccount->refresh(),
                'investment' => $investment,
                'movement' => $movement,
            ];
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
