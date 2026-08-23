<?php

namespace App\Models;

use App\Models\Concerns\Auditavel;
use App\Models\Concerns\PertenceAGranja;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Veiculo extends Model
{
    use Auditavel, HasFactory, PertenceAGranja;

    protected $table = 'veiculos';

    protected $fillable = [
        'granja_id',
        'placa',
        'modelo',
        'capacidade_carga',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'capacidade_carga' => 'decimal:2',
            'ativo' => 'boolean',
        ];
    }

    public function rotas(): HasMany
    {
        return $this->hasMany(Rota::class);
    }
}
