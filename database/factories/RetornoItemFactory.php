<?php

namespace Database\Factories;

use App\Models\Produto;
use App\Models\RetornoCaminhao;
use App\Models\RetornoItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class RetornoItemFactory extends Factory
{
    protected $model = RetornoItem::class;

    public function definition(): array
    {
        return [
            'retorno_caminhao_id' => RetornoCaminhao::factory(),
            'produto_id' => Produto::factory(),
            'quantidade' => $this->faker->numberBetween(1, 30),
            'motivo' => $this->faker->randomElement(['sobra', 'quebra', 'devolucao']),
        ];
    }
}
