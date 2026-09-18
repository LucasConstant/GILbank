<?php

namespace App\Services;

use App\Contracts\Repositories\AccountRepositoryInterface;
use App\Contracts\Repositories\MovementRepositoryInterface;
use App\Enums\MovementDirection;
use App\Enums\MovementType;
use App\Exceptions\BankingException;
use App\Models\Account;
use App\Models\Movement;
use Illuminate\Support\Facades\DB;

class TransferService
{
    public function __construct(
        private readonly AccountRepositoryInterface $accounts,
        private readonly MovementRepositoryInterface $movements,
    ) {}

    /**
     * @return array{origin: Account, destination: Account, outbound: Movement, inbound: Movement}
     */
    public function pix(Account $origin, int $destinationAccountId, string|float $amount, ?string $description = null): array
    {
        if ($origin->isBlocked()) {
            throw BankingException::accountBlocked();
        }

        $value = $this->normalizeMoney($amount);

        if ($origin->id === $destinationAccountId) {
            throw BankingException::sameAccount();
        }

        return DB::transaction(function () use ($origin, $destinationAccountId, $value, $description): array {
            $lockedOrigin = $this->accounts->findByIdForUpdate($origin->id);
            $lockedDestination = $this->accounts->findByIdForUpdate($destinationAccountId);

            if ($lockedOrigin === null || $lockedDestination === null) {
                throw BankingException::destinationNotFound();
            }

            if ($lockedOrigin->isBlocked() || $lockedDestination->isBlocked()) {
                throw BankingException::accountBlocked();
            }

            if (bccomp($lockedOrigin->availableBalance(), $value, 2) < 0) {
                throw BankingException::insufficientFunds();
            }

            $this->accounts->update($lockedOrigin, [
                'saldo' => bcsub((string) $lockedOrigin->saldo, $value, 2),
            ]);

            $this->accounts->update($lockedDestination, [
                'saldo' => bcadd((string) $lockedDestination->saldo, $value, 2),
            ]);

            $outbound = $this->movements->create([
                'conta_id' => $lockedOrigin->id,
                'tipo' => MovementType::PixSent,
                'valor' => $value,
                'direcao' => MovementDirection::Outbound,
                'conta_destino_id' => $lockedDestination->id,
                'descricao' => $description ?: 'Pix para conta #'.$lockedDestination->id,
            ]);

            $inbound = $this->movements->create([
                'conta_id' => $lockedDestination->id,
                'tipo' => MovementType::PixReceived,
                'valor' => $value,
                'direcao' => MovementDirection::Inbound,
                'conta_destino_id' => $lockedOrigin->id,
                'descricao' => 'Pix recebido da conta #'.$lockedOrigin->id,
            ]);

            return [
                'origin' => $lockedOrigin->refresh(),
                'destination' => $lockedDestination->refresh(),
                'outbound' => $outbound,
                'inbound' => $inbound,
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
