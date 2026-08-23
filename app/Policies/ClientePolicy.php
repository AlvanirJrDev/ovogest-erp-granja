<?php

namespace App\Policies;

use App\Models\Cliente;
use App\Models\User;

/**
 * Vendedor pode consultar e cadastrar estabelecimentos em campo;
 * exclusão fica restrita ao admin.
 */
class ClientePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'dono', 'vendedor']);
    }

    public function view(User $user, Cliente $cliente): bool
    {
        return $user->hasAnyRole(['admin', 'dono', 'vendedor']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'vendedor']);
    }

    public function update(User $user, Cliente $cliente): bool
    {
        return $user->hasAnyRole(['admin', 'vendedor']);
    }

    public function delete(User $user, Cliente $cliente): bool
    {
        return $user->hasRole('admin');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
