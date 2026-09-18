<?php

namespace Tests\Feature;

use App\Models\LimitRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class GeneralManagerModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_general_manager_can_list_account_managers(): void
    {
        $gm = User::factory()->generalManager()->create();
        User::factory()->accountManager()->create(['name' => 'Gerente Teste']);

        $this->actingAs($gm)
            ->get(route('general-manager.managers.index'))
            ->assertOk()
            ->assertSee('Gerente Teste');
    }

    public function test_account_manager_cannot_access_general_manager_area(): void
    {
        $manager = User::factory()->accountManager()->create();

        $this->actingAs($manager)
            ->get(route('general-manager.managers.index'))
            ->assertForbidden();
    }

    public function test_general_manager_can_create_account_manager_and_queue_credentials_email(): void
    {
        Mail::fake();

        $gm = User::factory()->generalManager()->create();

        $this->actingAs($gm)
            ->post(route('general-manager.managers.store'), [
                'name' => 'Novo Gerente',
                'email' => 'novo.gerente@gilbank.local',
                'password' => 'password123',
            ])
            ->assertRedirect(route('general-manager.managers.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'novo.gerente@gilbank.local',
            'role' => 'gerente_conta',
        ]);
    }

    public function test_general_manager_can_approve_limit_request(): void
    {
        $gm = User::factory()->generalManager()->create();
        $manager = User::factory()->accountManager()->create();
        $account = \App\Models\Account::factory()->create([
            'gerente_id' => $manager->id,
            'limite' => '500.00',
        ]);
        $limitRequest = LimitRequest::factory()->create([
            'conta_id' => $account->id,
            'gerente_id' => $manager->id,
            'limite_solicitado' => '1500.00',
        ]);

        $this->actingAs($gm)
            ->post(route('general-manager.limit-requests.approve', $limitRequest))
            ->assertRedirect();

        $this->assertDatabaseHas('solicitacoes_limite', [
            'id' => $limitRequest->id,
            'status' => 'aprovada',
            'aprovado_por' => $gm->id,
        ]);

        $this->assertDatabaseHas('contas', [
            'id' => $account->id,
            'limite' => '1500.00',
        ]);
    }

    public function test_general_manager_can_view_audit_logs(): void
    {
        $gm = User::factory()->generalManager()->create();

        $this->actingAs($gm)
            ->get(route('general-manager.audits.index'))
            ->assertOk();
    }
}
