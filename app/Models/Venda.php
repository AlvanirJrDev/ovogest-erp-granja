<?php

namespace App\Models;

use App\Enums\FormaPagamento;
use App\Enums\StatusCarga;
use App\Enums\StatusPagamento;
use App\Models\Concerns\Auditavel;
use App\Models\Concerns\PertenceAGranja;
use App\Services\NumeracaoService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Venda extends Model
{
    use Auditavel, HasFactory, PertenceAGranja;

    protected $table = 'vendas';

    protected $fillable = [
        'granja_id',
        'carga_caminhao_id',
        'cliente_id',
        'vendedor_id',
        'numero',
        'data_hora',
        'forma_pagamento',
        'valor_pago',
        'data_vencimento',
    ];

    protected function casts(): array
    {
        return [
            'data_hora' => 'datetime',
            'forma_pagamento' => FormaPagamento::class,
            'valor_pago' => 'decimal:2',
            'data_vencimento' => 'date',
            'cancelada_em' => 'datetime',
        ];
    }

    /** Vendas válidas: exclui as canceladas de saldos, conciliação e relatórios. */
    public function scopeAtivas($query)
    {
        return $query->whereNull('cancelada_em');
    }

    protected static function booted(): void
    {
        static::creating(function (Venda $venda) {
            $venda->data_hora ??= now();
            $venda->vendedor_id ??= auth()->id();

            $carga = CargaCaminhao::withoutGlobalScopes()->find($venda->carga_caminhao_id);

            $venda->granja_id ??= $carga?->granja_id;

            if ($carga !== null && $carga->status !== StatusCarga::Fechada) {
                throw ValidationException::withMessages([
                    'carga_caminhao_id' => 'Vendas só podem ser registradas em cargas fechadas (em rota). Cargas abertas ainda estão em carregamento e cargas conciliadas já foram encerradas.',
                ]);
            }

            // Anti clique-duplo: mesma venda (carga + cliente + vendedor)
            // em menos de 30 segundos é quase certamente reenvio acidental.
            $duplicada = static::withoutGlobalScopes()
                ->where('carga_caminhao_id', $venda->carga_caminhao_id)
                ->where('cliente_id', $venda->cliente_id)
                ->where('vendedor_id', $venda->vendedor_id)
                ->whereNull('cancelada_em')
                ->where('created_at', '>=', now()->subSeconds(30))
                ->exists();

            if ($duplicada) {
                throw ValidationException::withMessages([
                    'cliente_id' => 'Uma venda idêntica para este cliente foi registrada há menos de 30 segundos — provável clique duplo. Confira a lista de vendas; se for realmente um novo pedido, aguarde alguns instantes.',
                ]);
            }

            // Numera por último: venda bloqueada não consome número da sequência
            $venda->numero ??= NumeracaoService::proximo('venda', $venda->granja_id);
        });

        static::updating(function (Venda $venda) {
            // Venda cancelada é imutável (o próprio ato de cancelar é a
            // única transição permitida — cancelada_em sai de null).
            if ($venda->getOriginal('cancelada_em') !== null) {
                throw ValidationException::withMessages([
                    'cancelada_em' => 'Esta venda foi cancelada e não pode ser alterada (auditoria).',
                ]);
            }
        });

        static::deleting(function (Venda $venda) {
            if ($venda->cancelada_em !== null) {
                throw ValidationException::withMessages([
                    'cancelada_em' => 'Vendas canceladas permanecem no histórico e não podem ser excluídas (auditoria).',
                ]);
            }

            $carga = $venda->carga()->withoutGlobalScopes()->first();

            if ($carga?->status === StatusCarga::Conciliada) {
                throw ValidationException::withMessages([
                    'carga_caminhao_id' => 'Esta venda pertence a uma carga já conciliada e não pode ser excluída (auditoria).',
                ]);
            }
        });
    }

    public function carga(): BelongsTo
    {
        return $this->belongsTo(CargaCaminhao::class, 'carga_caminhao_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function itens(): HasMany
    {
        return $this->hasMany(VendaItem::class, 'venda_id');
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    public function recebimentos(): HasMany
    {
        return $this->hasMany(Recebimento::class);
    }

    public function getValorTotalAttribute(): float
    {
        return (float) $this->itens()
            ->selectRaw('coalesce(sum(quantidade * valor_unitario), 0) as total')
            ->value('total');
    }

    /** Pago no ato + baixas posteriores registradas pelo financeiro. */
    public function getValorRecebidoAttribute(): float
    {
        return (float) $this->valor_pago + (float) $this->recebimentos()->sum('valor');
    }

    public function getValorEmAbertoAttribute(): float
    {
        return max($this->valor_total - $this->valor_recebido, 0);
    }

    public function getStatusPagamentoAttribute(): StatusPagamento
    {
        if ($this->cancelada_em !== null) {
            return StatusPagamento::Cancelada;
        }

        if ($this->valor_em_aberto <= 0) {
            return StatusPagamento::Pago;
        }

        return $this->valor_recebido > 0
            ? StatusPagamento::Parcial
            : StatusPagamento::EmAberto;
    }

    /**
     * Cancelamento rastreável: a venda permanece no histórico com autor,
     * data e motivo, mas sai dos saldos, da conciliação e dos relatórios.
     * Só é permitido enquanto a carga não foi conciliada — depois disso a
     * equação saída = vendas + retorno já está fechada.
     */
    public function cancelar(string $motivo): void
    {
        if ($this->cancelada_em !== null) {
            throw ValidationException::withMessages([
                'cancelada_em' => 'Esta venda já está cancelada — nenhuma nova alteração é permitida (auditoria).',
            ]);
        }

        $carga = $this->carga()->withoutGlobalScopes()->first();

        if ($carga?->status === StatusCarga::Conciliada) {
            throw ValidationException::withMessages([
                'carga_caminhao_id' => 'A carga desta venda já foi conciliada — o cancelamento alteraria um fechamento auditado. Registre um ajuste na próxima carga.',
            ]);
        }

        $this->forceFill([
            'cancelada_em' => now(),
            'cancelada_por' => auth()->id(),
            'motivo_cancelamento' => $motivo,
        ])->save();
    }
}
