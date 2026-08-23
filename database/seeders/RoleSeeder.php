<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Dono da plataforma (SaaS): cadastra granjas e seus donos
        Role::findOrCreate('super_admin');

        // Perfis por granja
        Role::findOrCreate('dono');
        Role::findOrCreate('admin');
        Role::findOrCreate('financeiro');
        Role::findOrCreate('vendedor');
        Role::findOrCreate('producao');

        // Perfis reservados para as próximas fases
        Role::findOrCreate('motorista');
        Role::findOrCreate('cliente');
    }
}
