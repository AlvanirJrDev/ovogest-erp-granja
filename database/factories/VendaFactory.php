<?php

namespace Database\Factories;

use App\Models\CargaCaminhao;
use App\Models\Cliente;
use App\Models\Venda;
use Illuminate\Database\Eloquent\Factories\Factory;

class VendaFactory extends Factory
{
    protected $model = Venda::class;

    public function definition(): array
    {
        return [
            'carga_caminhao_id' => CargaCaminhao::factory(),
            'cliente_id' => Cliente::factory(),
            'data_hora' => now(),
            'forma_pagamento' => $this->faker->randomElement(['dinheiro', 'pix', 'prazo']),
        ];
    }
}
