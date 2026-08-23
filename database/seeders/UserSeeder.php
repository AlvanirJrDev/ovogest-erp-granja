<?php

namespace Database\Seeders;

use App\Models\Granja;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Super admin da plataforma (sem granja). Em produção, defina
        // SUPERADMIN_EMAIL/SUPERADMIN_PASSWORD no .env antes de rodar o seed.
        $superAdmin = User::firstOrCreate(
            ['email' => env('SUPERADMIN_EMAIL', 'superadmin@ovogest.test')],
            ['name' => 'Super Admin OvoGest', 'password' => env('SUPERADMIN_PASSWORD', 'password')],
        );
        $superAdmin->syncRoles(['super_admin']);

        // Granja e usuários de demonstração: apenas em ambiente local
        if (! app()->environment('local')) {
            return;
        }

        $granja = Granja::firstOrCreate(
            ['documento' => '12.345.678/0001-00'],
            [
                'nome' => 'Granja São José',
                'razao_social' => 'Granja São José Ltda',
                'email' => 'contato@granjasaojose.test',
                'telefone' => '(62) 3333-0000',
                'endereco' => 'Rodovia GO-010, km 12 — Zona Rural',
            ],
        );

        $usuarios = [
            ['email' => 'dono@granja.test', 'name' => 'Dono da Granja', 'role' => 'dono'],
            ['email' => 'admin@granja.test', 'name' => 'Administrador', 'role' => 'admin'],
            ['email' => 'financeiro@granja.test', 'name' => 'Ana Financeiro', 'role' => 'financeiro'],
            ['email' => 'vendedor@granja.test', 'name' => 'Carlos Vendedor', 'role' => 'vendedor'],
            ['email' => 'producao@granja.test', 'name' => 'José da Produção', 'role' => 'producao'],
        ];

        foreach ($usuarios as $dados) {
            $user = User::firstOrCreate(
                ['email' => $dados['email']],
                ['name' => $dados['name'], 'password' => 'password'],
            );
            $user->update(['granja_id' => $granja->id]);
            $user->syncRoles([$dados['role']]);
        }
    }
}
