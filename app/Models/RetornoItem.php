<?php

namespace App\Models;

use App\Enums\MotivoRetorno;
use App\Enums\StatusRetorno;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class RetornoItem extends Model
{
    use HasFactory;

    protected $table = 'retorno_itens';

    protected $fillable = [
        'retorno_caminhao_id',
        'produto_id',
        'quantidade',
        'motivo',
    ];

    protected function casts(): array
    {
        return [
            'quantidade' => 'integer',
            'motivo' => MotivoRetorno::class,
        ];
    }

    protected static function booted(): void
    {
        $bloquearSeFechado = function (RetornoItem $item) {
            $retorno = RetornoCaminhao::find($item->retorno_caminhao_id);

            if ($retorno !== null && $retorno->status === StatusRetorno::Fechado) {
                throw ValidationException::withMessages([
                    'retorno_caminhao_id' => 'Os itens de um retorno fechado não podem ser alterados (auditoria).',
                ]);
            }
        };

        static::creating($bloquearSeFechado);
        static::updating($bloquearSeFechado);
        static::deleting($bloquearSeFechado);

        // O caminhão não pode devolver mais do que saiu menos o que foi
        // vendido — barra erros de digitação antes de virarem divergência.
        $validarDisponivel = function (RetornoItem $item) {
            $retorno = RetornoCaminhao::withoutGlobalScopes()->find($item->retorno_caminhao_id);
            // Mesmo lock das vendas: lançamentos simultâneos são serializados
            $carga = $retorno === null ? null : CargaCaminhao::withoutGlobalScopes()
                ->lockForUpdate()
                ->find($retorno->carga_caminhao_id);

            if ($carga === null) {
                return;
            }

            $saiu = (int) $carga->itens()->where('produto_id', $item->produto_id)->sum('quantidade');

            $vendido = (int) VendaItem::where('produto_id', $item->produto_id)
                ->whereHas('venda', fn ($q) => $q->where('carga_caminhao_id', $carga->id)->whereNull('cancelada_em'))
                ->sum('quantidade');

            $jaRetornado = (int) static::where('produto_id', $item->produto_id)
                ->whereHas('retorno', fn ($q) => $q->where('carga_caminhao_id', $carga->id))
                ->when($item->exists, fn ($q) => $q->whereKeyNot($item->getKey()))
                ->sum('quantidade');

            $disponivel = $saiu - $vendido - $jaRetornado;

            if ($item->quantidade > $disponivel) {
                $produto = Produto::withoutGlobalScopes()->find($item->produto_id);

                throw ValidationException::withMessages([
                    'quantidade' => sprintf(
                        'Retorno maior que o disponível para "%s": restam %d bandeja(s) na carga #%d (saiu %d, vendido %d, já retornado %d).',
                        $produto?->nome ?? 'produto',
                        max($disponivel, 0),
                        $carga->numero,
                        $saiu,
                        $vendido,
                        $jaRetornado,
                    ),
                ]);
            }
        };

        static::creating($validarDisponivel);
        static::updating($validarDisponivel);
    }

    public function retorno(): BelongsTo
    {
        return $this->belongsTo(RetornoCaminhao::class, 'retorno_caminhao_id');
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }
}
