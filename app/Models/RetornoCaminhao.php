<?php

namespace App\Models;

use App\Enums\MotivoRetorno;
use App\Enums\StatusCarga;
use App\Enums\StatusRetorno;
use App\Enums\StatusRota;
use App\Enums\TipoMovimentoEstoque;
use App\Models\Concerns\Auditavel;
use App\Models\Concerns\PertenceAGranja;
use App\Services\ConciliacaoService;
use App\Services\NumeracaoService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RetornoCaminhao extends Model
{
    use Auditavel, HasFactory, PertenceAGranja;

    protected $table = 'retornos_caminhao';

    protected $fillable = [
        'granja_id',
        'carga_caminhao_id',
        'numero',
        'data_hora_retorno',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'data_hora_retorno' => 'datetime',
            'status' => StatusRetorno::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (RetornoCaminhao $retorno) {
            $retorno->data_hora_retorno ??= now();
            $retorno->status ??= StatusRetorno::Aberto;

            $carga = CargaCaminhao::withoutGlobalScopes()->find($retorno->carga_caminhao_id);

            $retorno->granja_id ??= $carga?->granja_id;

            if ($carga !== null && $carga->status !== StatusCarga::Fechada) {
                throw ValidationException::withMessages([
                    'carga_caminhao_id' => 'O retorno só pode ser registrado para uma carga fechada (em rota).',
                ]);
            }

            // Numera por último: retorno bloqueado não consome número da sequência
            $retorno->numero ??= NumeracaoService::proximo('retorno', $retorno->granja_id);
        });

        static::deleting(function (RetornoCaminhao $retorno) {
            if ($retorno->status === StatusRetorno::Fechado) {
                throw ValidationException::withMessages([
                    'status' => 'Um retorno fechado não pode ser excluído (auditoria).',
                ]);
            }
        });
    }

    public function carga(): BelongsTo
    {
        return $this->belongsTo(CargaCaminhao::class, 'carga_caminhao_id');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(RetornoItem::class, 'retorno_caminhao_id');
    }

    /**
     * Fecha o retorno: torna a nota de entrada imutável, dispara o cálculo
     * da conciliação e encerra a carga.
     */
    public function fechar(): void
    {
        if ($this->status === StatusRetorno::Fechado) {
            throw ValidationException::withMessages([
                'status' => 'Este retorno já foi fechado — a conciliação da carga já foi calculada (auditoria).',
            ]);
        }

        DB::transaction(function () {
            $this->update(['status' => StatusRetorno::Fechado]);

            // Sobras e devoluções voltam ao estoque; quebra é perda
            foreach ($this->itens as $item) {
                if ($item->motivo !== MotivoRetorno::Quebra) {
                    MovimentoEstoque::create([
                        'granja_id' => $this->granja_id,
                        'produto_id' => $item->produto_id,
                        'tipo' => TipoMovimentoEstoque::Retorno,
                        'quantidade' => $item->quantidade,
                        'retorno_caminhao_id' => $this->id,
                        'observacao' => "Retorno #{$this->numero} ({$item->motivo->getLabel()})",
                    ]);
                }
            }

            ConciliacaoService::calcular($this->carga);

            $this->carga->update(['status' => StatusCarga::Conciliada]);

            // Ciclo completo: se todas as cargas da rota foram conciliadas,
            // a rota se encerra sozinha (fica registrado na auditoria quem
            // fechou o retorno que a finalizou).
            $rota = $this->carga->rota()->withoutGlobalScopes()->first();

            if ($rota !== null && ! $rota->cargas()->where('status', '!=', StatusCarga::Conciliada->value)->exists()) {
                $rota->update(['status' => StatusRota::Finalizada]);
            }
        });
    }
}
