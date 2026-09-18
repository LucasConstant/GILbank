<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->customer(),
            'gerente_id' => User::factory()->accountManager(),
            'saldo' => fake()->randomFloat(2, 100, 5000),
            'limite' => fake()->randomFloat(2, 0, 1000),
            'bloqueada' => false,
        ];
    }

    public function blocked(): static
    {
        return $this->state(fn (array $attributes) => [
            'bloqueada' => true,
        ]);
    }
}
