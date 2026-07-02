<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Servico;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Agendamento>
 */
class AgendamentoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'data_inicio' => Carbon::now()->format('Y-m-d H:i:s'),
            'data_fim' => Carbon::now()->addDays(1)->format('Y-m-d H:i:s'),
            'observacao' => $this->faker->text(100),
            'cliente_id' => Cliente::factory(),
            'valor_total' => $this->faker->randomFloat(2, 0, 100),
            'status' => $this->faker->randomElement(['agendado', 'concluido', 'cancelado']),

        ];
    }
}
