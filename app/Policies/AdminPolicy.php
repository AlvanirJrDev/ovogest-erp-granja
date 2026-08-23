<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Policy padrão dos recursos operacionais: somente o perfil admin
 * pode ver e alterar. O dono acessa apenas o dashboard executivo
 * e a conciliação (ver ConciliacaoPolicy).
 */
class AdminPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'dono']);
    }

    public function view(User $user, Model $model): bool
    {
        return $user->hasAnyRole(['admin', 'dono']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Model $model): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, Model $model): bool
    {
        return $user->hasRole('admin');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
