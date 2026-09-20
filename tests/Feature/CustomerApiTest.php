<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_login_and_logout(): void
    {
        $customer = User::factory()->customer()->create();
        Account::factory()->create(['user_id' => $customer->id, 'saldo' => '1000.00']);

        $response = $this->postJson('/api/login', [
            'email' => $customer->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('user.email', $customer->email)
            ->assertJsonStructure(['token', 'token_type', 'user', 'account']);

        $token = $response->json('token');

        $this->withToken($token)
            ->postJson('/api/logout')
            ->assertOk();
    }

    public function test_staff_cannot_login_via_api(): void
    {
        $manager = User::factory()->accountManager()->create();

        $this->postJson('/api/login', [
            'email' => $manager->email,
            'password' => 'password',
        ])->assertUnprocessable();
    }

    public function test_customer_can_view_balance_and_investments(): void
    {
        $customer = User::factory()->customer()->create();
        $account = Account::factory()->create([
            'user_id' => $customer->id,
            'saldo' => '2500.00',
            'limite' => '300.00',
        ]);

        Sanctum::actingAs($customer);

        $this->getJson('/api/balance')
            ->assertOk()
            ->assertJsonPath('account.id', $account->id)
            ->assertJsonPath('account.saldo', '2500.00');
    }

    public function test_customer_can_send_pix(): void
    {
        $originUser = User::factory()->customer()->create();
        $destinationUser = User::factory()->customer()->create();
        $origin = Account::factory()->create([
            'user_id' => $originUser->id,
            'saldo' => '1000.00',
            'limite' => '100.00',
        ]);
        $destination = Account::factory()->create([
            'user_id' => $destinationUser->id,
            'saldo' => '50.00',
        ]);

        Sanctum::actingAs($originUser);

        $this->postJson('/api/pix', [
            'conta_destino_id' => $destination->id,
            'valor' => '200.00',
            'descricao' => 'Teste pix',
        ])->assertCreated();

        $this->assertEquals('800.00', $origin->fresh()->saldo);
        $this->assertEquals('250.00', $destination->fresh()->saldo);
        $this->assertDatabaseHas('movimentacoes', [
            'conta_id' => $origin->id,
            'tipo' => 'pix_enviado',
            'valor' => '200.00',
        ]);
        $this->assertDatabaseHas('movimentacoes', [
            'conta_id' => $destination->id,
            'tipo' => 'pix_recebido',
            'valor' => '200.00',
        ]);
    }

    public function test_blocked_customer_can_view_balance_but_cannot_pix(): void
    {
        $customer = User::factory()->customer()->create();
        Account::factory()->blocked()->create([
            'user_id' => $customer->id,
            'saldo' => '900.00',
        ]);

        Sanctum::actingAs($customer);

        $this->getJson('/api/balance')->assertOk();
        $this->postJson('/api/pix', [
            'conta_destino_id' => Account::factory()->create()->id,
            'valor' => '10.00',
        ])->assertForbidden();
        $this->getJson('/api/statement?start_date='.now()->subDay()->toDateString().'&end_date='.now()->toDateString())
            ->assertForbidden();
    }

    public function test_customer_can_apply_and_redeem(): void
    {
        $customer = User::factory()->customer()->create();
        $account = Account::factory()->create([
            'user_id' => $customer->id,
            'saldo' => '1000.00',
            'limite' => '0.00',
        ]);

        Sanctum::actingAs($customer);

        $this->postJson('/api/investments/apply', [
            'tipo' => 'cdb',
            'valor' => '400.00',
        ])->assertCreated();

        $this->assertEquals('600.00', $account->fresh()->saldo);

        $this->postJson('/api/investments/redeem', [
            'tipo' => 'cdb',
            'valor' => '150.00',
        ])->assertCreated();

        $this->assertEquals('750.00', $account->fresh()->saldo);
    }

    public function test_customer_can_filter_statement_by_period(): void
    {
        $customer = User::factory()->customer()->create();
        Account::factory()->create(['user_id' => $customer->id]);

        Sanctum::actingAs($customer);

        $this->getJson('/api/statement?start_date='.now()->subMonth()->toDateString().'&end_date='.now()->toDateString())
            ->assertOk()
            ->assertJsonStructure(['data']);
    }
}
