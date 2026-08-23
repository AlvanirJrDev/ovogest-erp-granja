<?php

namespace Database\Factories;

use App\Models\Veiculo;
use Illuminate\Database\Eloquent\Factories\Factory;

class VeiculoFactory extends Factory
{
    protected $model = Veiculo::class;

    public function definition(): array
    {
        return [
            'placa' => strtoupper($this->faker->unique()->bothify('???#?##')),
            'modelo' => $this->faker->randomElement(['HR 2.5 Baú', 'Fiorino Furgão', 'VUC 3/4', 'Saveiro Robust']),
            'capacidade_carga' => $this->faker->numberBetween(200, 1200),
            'ativo' => true,
        ];
    }
}
