<?php

namespace App\Models;

use App\Enums\StatusRota;
use App\Models\Concerns\Auditavel;
use App\Models\Concerns\PertenceAGranja;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rota extends Model
{
    use Auditavel, HasFactory, PertenceAGranja;

    protected $table = 'rotas';

    protected $fillable = [
        'granja_id',
        'nome',
        'veiculo_id',
        'data',
        'responsavel_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'date',
            'status' => StatusRota::class,
        ];
    }

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class);
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    public function cargas(): HasMany
    {
        return $this->hasMany(CargaCaminhao::class);
    }

    public function clientes(): BelongsToMany
    {
        return $this->belongsToMany(Cliente::class, 'cliente_rota')->withTimestamps();
    }
}
