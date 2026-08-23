<?php

namespace App\Models;

use App\Enums\TipoBandeja;
use App\Models\Concerns\Auditavel;
use App\Models\Concerns\PertenceAGranja;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    use Auditavel, HasFactory, PertenceAGranja;

    protected $table = 'produtos';

    protected $fillable = [
        'granja_id',
        'nome',
        'tipo_bandeja',
        'preco_venda',
        'custo_unitario',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'tipo_bandeja' => TipoBandeja::class,
            'preco_venda' => 'decimal:2',
            'custo_unitario' => 'decimal:2',
            'ativo' => 'boolean',
        ];
    }

    /**
     * Nome para exibição em selects: produtos podem repetir o nome
     * variando apenas o tamanho da bandeja.
     */
    public function getNomeCompletoAttribute(): string
    {
        return "{$this->nome} — {$this->tipo_bandeja->getLabel()}";
    }

    public function movimentosEstoque()
    {
        return $this->hasMany(MovimentoEstoque::class);
    }

    /**
     * Saldo em bandejas: producao + retornos - carregamentos +- ajustes.
     * Negativo indica carregamento sem producao lancada - nao bloqueia,
     * mas aparece em vermelho para cobrar o registro.
     */
    public function getEstoqueAtualAttribute(): int
    {
        return (int) $this->movimentosEstoque()->sum('quantidade');
    }
}
