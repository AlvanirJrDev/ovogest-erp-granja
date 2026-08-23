<?php

namespace App\Models;

use App\Enums\TipoMovimentoEstoque;
use App\Models\Concerns\PertenceAGranja;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

/**
 * Livro-razão do estoque de bandejas: produção entra, carregamento sai,
 * sobras/devoluções do retorno voltam. Movimentos nunca são editados;
 * lançamentos manuais errados (produção/ajuste) são excluídos e refeitos.
 */
class MovimentoEstoque extends Model
{
    use HasFactory, PertenceAGranja;

    protected $table = 'movimentos_estoque';

    protected $fillable = [
        'granja_id',
        'produto_id',
        'tipo',
        'quantidade',
        'data',
        'carga_caminhao_id',
        'retorno_caminhao_id',
        'usuario_id',
        'observacao',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoMovimentoEstoque::class,
            'quantidade' => 'integer',
            'data' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (MovimentoEstoque $movimento) {
            $movimento->granja_id ??= Produto::withoutGlobalScopes()->find($movimento->produto_id)?->granja_id;
            $movimento->usuario_id ??= auth()->id();
            $movimento->data ??= today();
        });

        static::updating(function () {
            throw ValidationException::withMessages([
                'tipo' => 'Movimentos de estoque são imutáveis — exclua e lance novamente.',
            ]);
        });

        static::deleting(function (MovimentoEstoque $movimento) {
            // Movimentos automáticos acompanham o documento que os gerou
            if (in_array($movimento->tipo, [TipoMovimentoEstoque::Carga, TipoMovimentoEstoque::Retorno], true)) {
                throw ValidationException::withMessages([
                    'tipo' => 'Movimentos gerados por carga ou retorno não podem ser excluídos — eles acompanham o documento que os criou (auditoria).',
                ]);
            }
        });
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
