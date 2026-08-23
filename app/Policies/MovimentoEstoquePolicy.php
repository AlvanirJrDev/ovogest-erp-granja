<?php

namespace App\Policies;

use App\Models\MovimentoEstoque;
use App\Models\User;

class MovimentoEstoquePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'dono', 'financeiro', 'producao']);
    }

    public function view(User $user, MovimentoEstoque $movimento): bool
    {
        return $user->hasAnyRole(['admin', 'dono', 'financeiro', 'producao']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'producao']);
    }

    public function update(User $user, MovimentoEstoque $movimento): bool
    {
        return false; // movimentos são imutáveis
    }

    public function delete(User $user, MovimentoEstoque $movimento): bool
    {
        return $user->hasRole('admin');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
