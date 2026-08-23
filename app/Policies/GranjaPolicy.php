<?php

namespace App\Policies;

use App\Models\Granja;
use App\Models\User;

/**
 * Granjas (tenants) são gerenciadas apenas pelo super admin da plataforma.
 */
class GranjaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function view(User $user, Granja $granja): bool
    {
        return $user->hasRole('super_admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function update(User $user, Granja $granja): bool
    {
        return $user->hasRole('super_admin');
    }

    public function delete(User $user, Granja $granja): bool
    {
        return $user->hasRole('super_admin');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }
}
