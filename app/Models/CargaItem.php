<?php

namespace App\Models;

use App\Enums\StatusCarga;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class CargaItem extends Model
{
    use HasFactory;

    protected $table = 'carga_itens';

    protected $fillable = [
        'carga_caminhao_id',
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
        $bloquearSeFechada = function (CargaItem $item) {
            $carga = CargaCaminhao::find($item->carga_caminhao_id);

            if ($carga !== null && $carga->status !== StatusCarga::Aberta) {
                throw ValidationException::withMessages([
                    'carga_caminhao_id' => 'Os itens de uma carga fechada não podem ser alterados (auditoria).',
                ]);
            }
        };

        static::creating($bloquearSeFechada);
        static::updating($bloquearSeFechada);
        static::deleting($bloquearSeFechada);
    }

    public function carga(): BelongsTo
    {
        return $this->belongsTo(CargaCaminhao::class, 'carga_caminhao_id');
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }
}
