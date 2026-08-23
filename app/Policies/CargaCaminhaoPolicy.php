<?php

namespace App\Policies;

use App\Models\CargaCaminhao;
use App\Models\User;

/**
 * Vendedor enxerga (somente leitura) as cargas das rotas em que é
 * o responsável, para acompanhar o saldo do caminhão. Carregamento,
 * fechamento e exclusão são do admin.
 */
class CargaCaminhaoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'dono', 'vendedor']);
    }

    public function view(User $user, CargaCaminhao $carga): bool
    {
        return $user->hasAnyRole(['admin', 'dono'])
            || ($user->hasRole('vendedor') && $carga->rota->responsavel_id === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, CargaCaminhao $carga): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, CargaCaminhao $carga): bool
    {
        return $user->hasRole('admin');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
