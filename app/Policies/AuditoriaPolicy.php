<?php

namespace App\Policies;

use App\Models\Auditoria;
use App\Models\User;

/** A auditoria é leitura pura — ninguém cria, edita ou apaga registros. */
class AuditoriaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin', 'dono']);
    }

    public function view(User $user, Auditoria $a): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin', 'dono']);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Auditoria $a): bool
    {
        return false;
    }

    public function delete(User $user, Auditoria $a): bool
    {
        return false;
    }
}
