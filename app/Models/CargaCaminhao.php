<?php

namespace App\Models;

use App\Enums\StatusCarga;
use App\Enums\TipoMovimentoEstoque;
use App\Models\Concerns\Auditavel;
use App\Models\Concerns\PertenceAGranja;
use App\Services\NumeracaoService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CargaCaminhao extends Model
{
    use Auditavel, HasFactory, PertenceAGranja;

    protected $table = 'cargas_caminhao';

    protected $fillable = [
        'granja_id',
        'rota_id',
        'numero',
        'data_hora_saida',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'data_hora_saida' => 'datetime',
            'status' => StatusCarga::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CargaCaminhao $carga) {
            $carga->granja_id ??= Rota::withoutGlobalScopes()->find($carga->rota_id)?->granja_id;
            $carga->numero ??= NumeracaoService::proximo('carga', $carga->granja_id);
            $carga->status ??= StatusCarga::Aberta;
        });

        static::deleting(function (CargaCaminhao $carga) {
            if ($carga->status !== StatusCarga::Aberta) {
                throw ValidationException::withMessages([
                    'status' => 'Uma carga fechada não pode ser excluída (auditoria) — registre um ajuste rastreável.',
                ]);
            }
        });
    }

    public function rota(): BelongsTo
    {
        return $this->belongsTo(Rota::class);
    }

    public function itens(): HasMany
    {
        return $this->hasMany(CargaItem::class, 'carga_caminhao_id');
    }

    public function vendas(): HasMany
    {
        return $this->hasMany(Venda::class, 'carga_caminhao_id');
    }

    public function retorno(): HasOne
    {
        return $this->hasOne(RetornoCaminhao::class, 'carga_caminhao_id');
    }

    public function conciliacao(): HasOne
    {
        return $this->hasOne(Conciliacao::class, 'carga_caminhao_id');
    }

    /**
     * Fecha a carga: valida os itens e torna a nota de saída imutável.
     * A partir daqui o caminhão está em rota e vendas podem ser registradas.
     */
    public function fechar(): void
    {
        if ($this->status !== StatusCarga::Aberta) {
            throw ValidationException::withMessages([
                'status' => 'Esta carga já foi fechada — os itens são imutáveis a partir do fechamento (auditoria).',
            ]);
        }

        if ($this->itens()->count() === 0) {
            throw ValidationException::withMessages([
                'itens' => 'A carga não pode ser fechada sem itens — adicione os produtos carregados antes de fechar.',
            ]);
        }

        if ($this->itens()->where('quantidade', '<=', 0)->exists()) {
            throw ValidationException::withMessages([
                'itens' => 'A carga não pode ser fechada com itens zerados — corrija as quantidades antes de fechar.',
            ]);
        }

        DB::transaction(function () {
            $this->update(['status' => StatusCarga::Fechada]);

            // O que sobe no caminhão sai do estoque da granja
            foreach ($this->itens as $item) {
                MovimentoEstoque::create([
                    'granja_id' => $this->granja_id,
                    'produto_id' => $item->produto_id,
                    'tipo' => TipoMovimentoEstoque::Carga,
                    'quantidade' => -$item->quantidade,
                    'carga_caminhao_id' => $this->id,
                    'observacao' => "Carregamento da carga #{$this->numero}",
                ]);
            }
        });
    }

    /**
     * Saldo disponível de um produto na carga: saiu - vendido - retornado.
     */
    public function saldoDisponivel(int $produtoId, ?int $ignorarVendaItemId = null): int
    {
        $saiu = (int) $this->itens()
            ->where('produto_id', $produtoId)
            ->sum('quantidade');

        $vendido = (int) VendaItem::where('produto_id', $produtoId)
            ->whereHas('venda', fn ($query) => $query->where('carga_caminhao_id', $this->id)->whereNull('cancelada_em'))
            ->when($ignorarVendaItemId, fn ($query) => $query->whereKeyNot($ignorarVendaItemId))
            ->sum('quantidade');

        $retornado = (int) RetornoItem::where('produto_id', $produtoId)
            ->whereHas('retorno', fn ($query) => $query->where('carga_caminhao_id', $this->id))
            ->sum('quantidade');

        return $saiu - $vendido - $retornado;
    }

    public function getValorTotalAttribute(): float
    {
        return (float) $this->itens()
            ->selectRaw('coalesce(sum(quantidade * valor_unitario), 0) as total')
            ->value('total');
    }
}
