<?php

namespace Database\Factories;

use App\Models\Produto;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProdutoFactory extends Factory
{
    protected $model = Produto::class;

    public function definition(): array
    {
        $custo = $this->faker->randomFloat(2, 8, 15);

        return [
            'nome' => 'Ovo '.$this->faker->unique()->words(2, true),
            'tipo_bandeja' => $this->faker->randomElement(['12', '15', '30']),
            'preco_venda' => $custo + $this->faker->randomFloat(2, 3, 8),
            'custo_unitario' => $custo,
            'ativo' => true,
        ];
    }
}
