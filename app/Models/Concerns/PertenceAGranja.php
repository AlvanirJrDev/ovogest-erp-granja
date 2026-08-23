<?php

namespace App\Models\Concerns;

use App\Models\Granja;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Isolamento multi-tenant: usuários vinculados a uma granja só enxergam
 * (e criam) registros da própria granja. O super admin da plataforma
 * (granja_id nulo) não sofre filtro.
 *
 * Não aplicar ao model User — o global scope consultaria auth() durante a
 * própria autenticação (recursão); o escopo de usuários é feito no resource.
 */
trait PertenceAGranja
{
    protected static function bootPertenceAGranja(): void
    {
        static::addGlobalScope('granja', function (Builder $query) {
            $user = auth()->user();

            if ($user !== null && $user->granja_id !== null) {
                $query->where(
                    $query->getModel()->getTable().'.granja_id',
                    $user->granja_id,
                );
            }
        });

        static::creating(function ($model) {
            $model->granja_id ??= auth()->user()?->granja_id;
        });
    }

    public function granja(): BelongsTo
    {
        return $this->belongsTo(Granja::class);
    }
}
