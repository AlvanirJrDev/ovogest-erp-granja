<?php

namespace Database\Factories;

use App\Models\CargaCaminhao;
use App\Models\CargaItem;
use App\Models\Produto;
use Illuminate\Database\Eloquent\Factories\Factory;

class CargaItemFactory extends Factory
{
    protected $model = CargaItem::class;

    public function definition(): array
    {
        return [
            'carga_caminhao_id' => CargaCaminhao::factory(),
            'produto_id' => Produto::factory(),
            'quantidade' => $this->faker->numberBetween(10, 200),
            'valor_unitario' => $this->faker->randomFloat(2, 12, 25),
        ];
    }
}
