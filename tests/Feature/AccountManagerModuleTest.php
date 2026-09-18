<?php

namespace Tests\Feature;

use App\Events\AccountBlockStatusChanged;
use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AccountManagerModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_manager_can_list_own_accounts(): void
    {
        $manager = User::factory()->accountManager()->create();
        $account = Account::factory()->create([
            'gerente_id' => $manager->id,
        ]);

        $this->actingAs($manager)
            ->get(route('account-manager.accounts.index'))
            ->assertOk()
            ->assertSee($account->customer->name);
    }

    public function test_account_manager_cannot_see_other_manager_accounts(): void
    {
        $manager = User::factory()->accountManager()->create();
        $other = User::factory()->accountManager()->create();
        $foreign = Account::factory()->create(['gerente_id' => $other->id]);

        $this->actingAs($manager)
            ->get(route('account-manager.accounts.show', $foreign))
            ->assertForbidden();
    }

    public function test_account_manager_can_create_customer_account(): void
    {
        Event::fake([AccountBlockStatusChanged::class]);

        $manager = User::factory()->accountManager()->create();

        $this->actingAs($manager)
            ->post(route('account-manager.accounts.store'), [
                'name' => 'Cliente Novo',
                'email' => 'cliente.novo@gilbank.local',
                'password' => 'password123',
                'saldo' => '1000.00',
                'limite' => '200.00',
            ])
            ->assertRedirect(route('account-manager.accounts.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'cliente.novo@gilbank.local',
            'role' => 'cliente',
        ]);

        $this->assertDatabaseHas('contas', [
            'gerente_id' => $manager->id,
            'saldo' => '1000.00',
            'limite' => '200.00',
        ]);
    }

    public function test_account_manager_can_request_limit_increase(): void
    {
        $manager = User::factory()->accountManager()->create();
        $account = Account::factory()->create([
            'gerente_id' => $manager->id,
            'limite' => '500.00',
        ]);

        $this->actingAs($manager)
            ->post(route('account-manager.limit-requests.store', $account), [
                'limite_solicitado' => '1200.00',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('solicitacoes_limite', [
            'conta_id' => $account->id,
            'gerente_id' => $manager->id,
            'limite_solicitado' => '1200.00',
            'status' => 'pendente',
        ]);
    }

    public function test_account_manager_can_block_and_unblock_account(): void
    {
        Event::fake([AccountBlockStatusChanged::class]);

        $manager = User::factory()->accountManager()->create();
        $account = Account::factory()->create([
            'gerente_id' => $manager->id,
            'bloqueada' => false,
        ]);

        $this->actingAs($manager)
            ->post(route('account-manager.accounts.block', $account))
            ->assertRedirect();

        $this->assertTrue($account->fresh()->isBlocked());
        Event::assertDispatched(AccountBlockStatusChanged::class);

        $this->actingAs($manager)
            ->post(route('account-manager.accounts.unblock', $account))
            ->assertRedirect();

        $this->assertFalse($account->fresh()->isBlocked());
    }

    public function test_account_manager_can_view_statement(): void
    {
        $manager = User::factory()->accountManager()->create();
        $account = Account::factory()->create(['gerente_id' => $manager->id]);

        $this->actingAs($manager)
            ->get(route('account-manager.accounts.statement', [
                'account' => $account,
                'start_date' => now()->subMonth()->toDateString(),
                'end_date' => now()->toDateString(),
            ]))
            ->assertOk();
    }
}
