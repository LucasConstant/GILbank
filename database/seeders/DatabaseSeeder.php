<?php

namespace Database\Seeders;

use App\Enums\InvestmentType;
use App\Enums\LimitRequestStatus;
use App\Enums\MovementDirection;
use App\Enums\MovementType;
use App\Enums\UserRole;
use App\Models\Account;
use App\Models\Investment;
use App\Models\LimitRequest;
use App\Models\Movement;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public const DEFAULT_PASSWORD = 'password';

    public function run(): void
    {
        $password = self::DEFAULT_PASSWORD;

        $generalManager = User::query()->create([
            'name' => 'Helena Costa',
            'email' => 'geral@gilbank.local',
            'password' => $password,
            'role' => UserRole::GeneralManager,
            'email_verified_at' => now(),
        ]);

        $managerMaria = User::query()->create([
            'name' => 'Maria Oliveira',
            'email' => 'maria.gerente@gilbank.local',
            'password' => $password,
            'role' => UserRole::AccountManager,
            'email_verified_at' => now(),
        ]);

        $managerJoao = User::query()->create([
            'name' => 'João Ferreira',
            'email' => 'joao.gerente@gilbank.local',
            'password' => $password,
            'role' => UserRole::AccountManager,
            'email_verified_at' => now(),
        ]);

        $customers = [
            [
                'name' => 'Ana Souza',
                'email' => 'ana.cliente@gilbank.local',
                'manager' => $managerMaria,
                'saldo' => '3500.00',
                'limite' => '800.00',
                'bloqueada' => false,
            ],
            [
                'name' => 'Bruno Lima',
                'email' => 'bruno.cliente@gilbank.local',
                'manager' => $managerMaria,
                'saldo' => '1200.50',
                'limite' => '500.00',
                'bloqueada' => false,
            ],
            [
                'name' => 'Carla Mendes',
                'email' => 'carla.cliente@gilbank.local',
                'manager' => $managerMaria,
                'saldo' => '80.00',
                'limite' => '200.00',
                'bloqueada' => true,
            ],
            [
                'name' => 'Diego Rocha',
                'email' => 'diego.cliente@gilbank.local',
                'manager' => $managerJoao,
                'saldo' => '5400.75',
                'limite' => '1500.00',
                'bloqueada' => false,
            ],
            [
                'name' => 'Elisa Martins',
                'email' => 'elisa.cliente@gilbank.local',
                'manager' => $managerJoao,
                'saldo' => '2100.00',
                'limite' => '600.00',
                'bloqueada' => false,
            ],
        ];

        $accounts = [];

        foreach ($customers as $customerData) {
            $user = User::query()->create([
                'name' => $customerData['name'],
                'email' => $customerData['email'],
                'password' => $password,
                'role' => UserRole::Customer,
                'email_verified_at' => now(),
            ]);

            $accounts[] = Account::query()->create([
                'user_id' => $user->id,
                'gerente_id' => $customerData['manager']->id,
                'saldo' => $customerData['saldo'],
                'limite' => $customerData['limite'],
                'bloqueada' => $customerData['bloqueada'],
            ]);
        }

        [$ana, $bruno, $carla, $diego, $elisa] = $accounts;

        $this->seedInvestments($ana, [
            InvestmentType::Cdb->value => '1000.00',
            InvestmentType::Cdi->value => '250.00',
            InvestmentType::Savings->value => '150.00',
        ]);
        $this->seedInvestments($bruno, [
            InvestmentType::Savings->value => '400.00',
        ]);
        $this->seedInvestments($diego, [
            InvestmentType::Cdb->value => '2000.00',
            InvestmentType::Cdi->value => '800.00',
        ]);
        $this->seedInvestments($elisa, [
            InvestmentType::Cdi->value => '300.00',
            InvestmentType::Savings->value => '500.00',
        ]);

        $this->seedPix($ana, $bruno, '150.00', now()->subDays(12), 'Pix para Bruno Lima');
        $this->seedPix($diego, $ana, '320.00', now()->subDays(8), 'Pix para Ana Souza');
        $this->seedPix($elisa, $bruno, '75.50', now()->subDays(3), 'Pix para Bruno Lima');

        $this->seedApplication($ana, InvestmentType::Cdb, '200.00', now()->subDays(10));
        $this->seedRedemption($diego, InvestmentType::Cdi, '100.00', now()->subDays(5));
        $this->seedApplication($elisa, InvestmentType::Savings, '50.00', now()->subDays(2));

        LimitRequest::query()->create([
            'conta_id' => $bruno->id,
            'gerente_id' => $managerMaria->id,
            'limite_solicitado' => '1200.00',
            'status' => LimitRequestStatus::Pending,
        ]);

        LimitRequest::query()->create([
            'conta_id' => $diego->id,
            'gerente_id' => $managerJoao->id,
            'limite_solicitado' => '2500.00',
            'status' => LimitRequestStatus::Approved,
            'aprovado_por' => $generalManager->id,
        ]);

        LimitRequest::query()->create([
            'conta_id' => $carla->id,
            'gerente_id' => $managerMaria->id,
            'limite_solicitado' => '400.00',
            'status' => LimitRequestStatus::Rejected,
            'aprovado_por' => $generalManager->id,
        ]);
    }

    /**
     * @param  array<string, string>  $balances
     */
    private function seedInvestments(Account $account, array $balances): void
    {
        foreach ($balances as $type => $amount) {
            Investment::query()->create([
                'conta_id' => $account->id,
                'tipo' => $type,
                'saldo_aplicado' => $amount,
            ]);
        }
    }

    private function seedPix(Account $origin, Account $destination, string $amount, \DateTimeInterface $when, string $description): void
    {
        Movement::query()->create([
            'conta_id' => $origin->id,
            'tipo' => MovementType::PixSent,
            'valor' => $amount,
            'direcao' => MovementDirection::Outbound,
            'conta_destino_id' => $destination->id,
            'descricao' => $description,
            'created_at' => $when,
            'updated_at' => $when,
        ]);

        Movement::query()->create([
            'conta_id' => $destination->id,
            'tipo' => MovementType::PixReceived,
            'valor' => $amount,
            'direcao' => MovementDirection::Inbound,
            'conta_destino_id' => $origin->id,
            'descricao' => 'Pix recebido de '.$origin->customer->name,
            'created_at' => $when,
            'updated_at' => $when,
        ]);
    }

    private function seedApplication(Account $account, InvestmentType $type, string $amount, \DateTimeInterface $when): void
    {
        Movement::query()->create([
            'conta_id' => $account->id,
            'tipo' => MovementType::Application,
            'valor' => $amount,
            'direcao' => MovementDirection::Outbound,
            'aplicacao_tipo' => $type,
            'descricao' => 'Aplicação em '.$type->label(),
            'created_at' => $when,
            'updated_at' => $when,
        ]);
    }

    private function seedRedemption(Account $account, InvestmentType $type, string $amount, \DateTimeInterface $when): void
    {
        Movement::query()->create([
            'conta_id' => $account->id,
            'tipo' => MovementType::Redemption,
            'valor' => $amount,
            'direcao' => MovementDirection::Inbound,
            'aplicacao_tipo' => $type,
            'descricao' => 'Resgate de '.$type->label(),
            'created_at' => $when,
            'updated_at' => $when,
        ]);
    }
}
