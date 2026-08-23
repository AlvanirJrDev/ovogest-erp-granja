<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

    public function definition(): array
    {
        return [
            'nome' => $this->faker->company(),
            'documento' => $this->faker->numerify('##.###.###/0001-##'),
            'telefone' => $this->faker->numerify('(##) 9####-####'),
            'endereco' => $this->faker->streetAddress(),
        ];
    }
}
