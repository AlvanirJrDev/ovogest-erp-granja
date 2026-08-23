<?php

namespace Database\Factories;

use App\Models\Produto;
use App\Models\Venda;
use App\Models\VendaItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class VendaItemFactory extends Factory
{
    protected $model = VendaItem::class;

    public function definition(): array
    {
        return [
            'venda_id' => Venda::factory(),
            'produto_id' => Produto::factory(),
            'quantidade' => $this->faker->numberBetween(1, 50),
            'valor_unitario' => $this->faker->randomFloat(2, 12, 25),
        ];
    }
}
