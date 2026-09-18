<?php

namespace Database\Factories;

use App\Enums\LimitRequestStatus;
use App\Models\Account;
use App\Models\LimitRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LimitRequest>
 */
class LimitRequestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conta_id' => Account::factory(),
            'gerente_id' => User::factory()->accountManager(),
            'limite_solicitado' => fake()->randomFloat(2, 500, 5000),
            'status' => LimitRequestStatus::Pending,
            'aprovado_por' => null,
        ];
    }

    public function approved(?User $reviewer = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LimitRequestStatus::Approved,
            'aprovado_por' => $reviewer?->id ?? User::factory()->generalManager(),
        ]);
    }

    public function rejected(?User $reviewer = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LimitRequestStatus::Rejected,
            'aprovado_por' => $reviewer?->id ?? User::factory()->generalManager(),
        ]);
    }
}
