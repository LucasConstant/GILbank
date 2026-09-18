<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Account;
use App\Models\LimitRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'role:'.UserRole::GeneralManager->value])
            ->get('/__test/general-manager', fn () => response('ok-gm'));

        Route::middleware(['web', 'auth', 'role:'.UserRole::AccountManager->value])
            ->get('/__test/account-manager', fn () => response('ok-am'));
    }

    public function test_role_middleware_allows_general_manager(): void
    {
        $user = User::factory()->generalManager()->create();

        $this->actingAs($user)
            ->get('/__test/general-manager')
            ->assertOk()
            ->assertSee('ok-gm');
    }

    public function test_role_middleware_blocks_account_manager_from_general_area(): void
    {
        $user = User::factory()->accountManager()->create();

        $this->actingAs($user)
            ->get('/__test/general-manager')
            ->assertForbidden();
    }

    public function test_account_policy_restricts_manager_to_own_accounts(): void
    {
        $manager = User::factory()->accountManager()->create();
        $otherManager = User::factory()->accountManager()->create();

        $ownAccount = Account::factory()->create(['gerente_id' => $manager->id]);
        $foreignAccount = Account::factory()->create(['gerente_id' => $otherManager->id]);

        $this->assertTrue(Gate::forUser($manager)->allows('view', $ownAccount));
        $this->assertTrue(Gate::forUser($manager)->denies('view', $foreignAccount));
        $this->assertTrue(Gate::forUser($manager)->allows('block', $ownAccount));
        $this->assertTrue(Gate::forUser($manager)->denies('block', $foreignAccount));
    }

    public function test_limit_request_policy_only_general_manager_can_review(): void
    {
        $generalManager = User::factory()->generalManager()->create();
        $manager = User::factory()->accountManager()->create();
        $account = Account::factory()->create(['gerente_id' => $manager->id]);

        $request = LimitRequest::factory()->create([
            'conta_id' => $account->id,
            'gerente_id' => $manager->id,
        ]);

        $this->assertTrue(Gate::forUser($generalManager)->allows('approve', $request));
        $this->assertTrue(Gate::forUser($manager)->denies('approve', $request));
    }

    public function test_blocked_customer_cannot_view_statement_via_policy(): void
    {
        $customer = User::factory()->customer()->create();
        $manager = User::factory()->accountManager()->create();
        $account = Account::factory()->blocked()->create([
            'user_id' => $customer->id,
            'gerente_id' => $manager->id,
        ]);

        $this->assertTrue(Gate::forUser($customer)->allows('view', $account));
        $this->assertTrue(Gate::forUser($customer)->denies('viewStatement', $account));
        $this->assertTrue(Gate::forUser($customer)->denies('transfer', $account));
    }

    public function test_staff_gate_helpers(): void
    {
        $generalManager = User::factory()->generalManager()->create();
        $manager = User::factory()->accountManager()->create();
        $customer = User::factory()->customer()->create();

        $this->assertTrue(Gate::forUser($generalManager)->allows('access-general-manager-area'));
        $this->assertTrue(Gate::forUser($manager)->allows('access-account-manager-area'));
        $this->assertTrue(Gate::forUser($customer)->denies('access-staff-area'));
    }
}
