<?php

namespace App\Models;

use App\Enums\StatusCarga;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class VendaItem extends Model
{
    use HasFactory;

    protected $table = 'venda_itens';

    protected $fillable = [
        'venda_id',
        'produto_id',
        'quantidade',
        'valor_unitario',
    ];

    protected function casts(): array
    {
        return [
            'quantidade' => 'integer',
            'valor_unitario' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        // Após a conciliação a venda é imutável: alterar itens quebraria
        // a equação saída = vendas + retorno já fechada.
        $bloquearSeConciliada = function (VendaItem $item) {
            $venda = Venda::withoutGlobalScopes()->find($item->venda_id);

            if ($venda?->cancelada_em !== null) {
                throw ValidationException::withMessages([
                    'venda_id' => 'Esta venda foi cancelada e não pode ser alterada (auditoria).',
                ]);
            }

            $carga = $venda?->carga()->withoutGlobalScopes()->first();

            if ($carga?->status === StatusCarga::Conciliada) {
                throw ValidationException::withMessages([
                    'venda_id' => 'Esta venda pertence a uma carga já conciliada e não pode ser alterada (auditoria).',
                ]);
            }
        };

        static::creating($bloquearSeConciliada);
        static::updating($bloquearSeConciliada);
        static::deleting($bloquearSeConciliada);

        $validarSaldo = function (VendaItem $item) {
            $venda = Venda::find($item->venda_id);

            if ($venda === null) {
                return;
            }

            // Lock pessimista: vendas simultâneas na mesma carga são
            // serializadas — impossível duas passarem juntas pela validação
            // e estourarem o saldo do caminhão.
            $carga = CargaCaminhao::withoutGlobalScopes()
                ->lockForUpdate()
                ->find($venda->carga_caminhao_id);

            if ($carga === null) {
                return;
            }
            $saldo = $carga->saldoDisponivel(
                $item->produto_id,
                $item->exists ? $item->id : null,
            );

            if ($item->quantidade > $saldo) {
                $produto = Produto::find($item->produto_id);

                throw ValidationException::withMessages([
                    'quantidade' => sprintf(
                        'Quantidade indisponível para "%s": saldo na carga #%d é de %d bandeja(s).',
                        $produto?->nome ?? 'produto',
                        $carga->numero,
                        max($saldo, 0),
                    ),
                ]);
            }
        };

        static::creating($validarSaldo);
        static::updating($validarSaldo);
    }

    public function venda(): BelongsTo
    {
        return $this->belongsTo(Venda::class);
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }
}
