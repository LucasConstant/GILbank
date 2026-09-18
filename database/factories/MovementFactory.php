<?php

namespace Database\Factories;

use App\Enums\MovementDirection;
use App\Enums\MovementType;
use App\Models\Account;
use App\Models\Movement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Movement>
 */
class MovementFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conta_id' => Account::factory(),
            'tipo' => MovementType::PixReceived,
            'valor' => fake()->randomFloat(2, 10, 800),
            'direcao' => MovementDirection::Inbound,
            'conta_destino_id' => null,
            'aplicacao_tipo' => null,
            'descricao' => 'Movimentação de teste',
        ];
    }
}
