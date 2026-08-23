<?php

namespace App\Policies;

use App\Models\Conciliacao;
use App\Models\User;

/**
 * A conciliação é calculada pelo sistema e nunca editada manualmente —
 * nem pelo admin. Admin e dono podem visualizar.
 */
class ConciliacaoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'dono', 'financeiro']);
    }

    public function view(User $user, Conciliacao $conciliacao): bool
    {
        return $user->hasAnyRole(['admin', 'dono', 'financeiro']);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Conciliacao $conciliacao): bool
    {
        return false;
    }

    public function delete(User $user, Conciliacao $conciliacao): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
