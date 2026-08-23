<?php

namespace App\Models;

use App\Enums\StatusConciliacao;
use App\Models\Concerns\PertenceAGranja;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Conciliacao extends Model
{
    use HasFactory, PertenceAGranja;

    protected $table = 'conciliacoes';

    protected $fillable = [
        'carga_caminhao_id',
        'granja_id',
        'total_saiu',
        'total_vendido',
        'total_retornou',
        'diferenca',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_saiu' => 'integer',
            'total_vendido' => 'integer',
            'total_retornou' => 'integer',
            'diferenca' => 'integer',
            'status' => StatusConciliacao::class,
        ];
    }

    public function carga(): BelongsTo
    {
        return $this->belongsTo(CargaCaminhao::class, 'carga_caminhao_id');
    }
}
