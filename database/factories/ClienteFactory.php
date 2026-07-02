<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cliente>
 */
class ClienteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => $this->faker->name(),
            'telefone' => $this->faker->phoneNumber(),
            'email' => $this->faker->email(),
            'tipo' => $this->faker->randomElement(['PF', 'PJ']),
            'documento' => fake()->cpf(),
            'endereco' => $this->faker->address(),
            'data_nascimento' => $this->faker->date(),
            'observacao' => $this->faker->text(),
            'user_id' => User::factory(),
        ];
    }
}
