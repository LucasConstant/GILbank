<?php

namespace Database\Factories;

use App\Enums\InvestmentType;
use App\Models\Account;
use App\Models\Investment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Investment>
 */
class InvestmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conta_id' => Account::factory(),
            'tipo' => fake()->randomElement(InvestmentType::cases()),
            'saldo_aplicado' => fake()->randomFloat(2, 50, 2000),
        ];
    }
}
