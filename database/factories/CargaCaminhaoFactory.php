<?php

namespace Database\Factories;

use App\Models\CargaCaminhao;
use App\Models\Rota;
use Illuminate\Database\Eloquent\Factories\Factory;

class CargaCaminhaoFactory extends Factory
{
    protected $model = CargaCaminhao::class;

    public function definition(): array
    {
        return [
            'rota_id' => Rota::factory(),
            'data_hora_saida' => now(),
            'status' => 'aberta',
        ];
    }
}
