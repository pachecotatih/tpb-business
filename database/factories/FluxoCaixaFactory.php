<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FluxoCaixa>
 */
class FluxoCaixaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'descricao' => fake()->sentence(),
            'valor' => fake()->randomFloat(2, 1, 1000),
            'tipo_movimentacao' => fake()->randomElement(['entrada', 'saida']),
            'forma_pagamento' => fake()->randomElement(['dinheiro', 'cartao', 'pix']),
            'pago' => fake()->boolean(),
            'observacao' => fake()->paragraph(),
            'user_id' => User::factory()->create()->id,
        ];
    }
}
