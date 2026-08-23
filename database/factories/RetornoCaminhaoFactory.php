<?php

namespace Database\Factories;

use App\Models\CargaCaminhao;
use App\Models\RetornoCaminhao;
use Illuminate\Database\Eloquent\Factories\Factory;

class RetornoCaminhaoFactory extends Factory
{
    protected $model = RetornoCaminhao::class;

    public function definition(): array
    {
        return [
            'carga_caminhao_id' => CargaCaminhao::factory(),
            'data_hora_retorno' => now(),
            'status' => 'aberto',
        ];
    }
}
