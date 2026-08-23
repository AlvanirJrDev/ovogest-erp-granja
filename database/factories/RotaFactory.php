<?php

namespace Database\Factories;

use App\Models\Rota;
use App\Models\User;
use App\Models\Veiculo;
use Illuminate\Database\Eloquent\Factories\Factory;

class RotaFactory extends Factory
{
    protected $model = Rota::class;

    public function definition(): array
    {
        return [
            'nome' => 'Rota '.$this->faker->city(),
            'veiculo_id' => Veiculo::factory(),
            'data' => now()->toDateString(),
            'responsavel_id' => User::factory(),
            'status' => 'planejada',
        ];
    }
}
