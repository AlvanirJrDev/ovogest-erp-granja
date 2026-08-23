<?php

namespace App\Policies;

use App\Models\User;

/**
 * Super admin gerencia todos os usuários (cria granjas e seus donos).
 * O dono cria e gerencia os acessos da própria granja
 * (admin, financeiro, vendedor, motorista) — nunca outros donos/super admins.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'dono']);
    }

    public function view(User $user, User $alvo): bool
    {
        return $this->podeGerenciar($user, $alvo);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'dono']);
    }

    public function update(User $user, User $alvo): bool
    {
        return $this->podeGerenciar($user, $alvo);
    }

    public function delete(User $user, User $alvo): bool
    {
        return $user->isNot($alvo) && $this->podeGerenciar($user, $alvo);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'dono']);
    }

    private function podeGerenciar(User $user, User $alvo): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->hasRole('dono')
            && $alvo->granja_id === $user->granja_id
            && ! $alvo->hasAnyRole(['super_admin', 'dono']);
    }
}
