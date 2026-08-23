<?php

namespace App\Models;

use App\Models\Concerns\Auditavel;
use App\Models\Concerns\PertenceAGranja;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

/**
 * Baixa de pagamento de uma venda a prazo/parcial: quem recebeu,
 * quando e quanto. O histórico nunca é editado — recebimento errado
 * é excluído (admin) e lançado de novo.
 */
class Recebimento extends Model
{
    use Auditavel, HasFactory, PertenceAGranja;

    protected $table = 'recebimentos';

    protected $fillable = [
        'granja_id',
        'venda_id',
        'valor',
        'forma',
        'data',
        'recebido_por',
        'observacao',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'data' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Recebimento $recebimento) {
            $venda = Venda::withoutGlobalScopes()->find($recebimento->venda_id);

            $recebimento->granja_id ??= $venda?->granja_id;
            $recebimento->recebido_por ??= auth()->id();
            $recebimento->data ??= today();

            if ($venda?->cancelada_em !== null) {
                throw ValidationException::withMessages([
                    'venda_id' => 'Esta venda foi cancelada e não recebe baixas de pagamento (auditoria).',
                ]);
            }

            if ($venda !== null && (float) $recebimento->valor > $venda->valor_em_aberto + 0.009) {
                throw ValidationException::withMessages([
                    'valor' => sprintf(
                        'Valor maior que o saldo em aberto da venda #%d (R$ %s).',
                        $venda->numero,
                        number_format($venda->valor_em_aberto, 2, ',', '.'),
                    ),
                ]);
            }
        });
    }

    public function venda(): BelongsTo
    {
        return $this->belongsTo(Venda::class);
    }

    public function recebedor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recebido_por');
    }
}
