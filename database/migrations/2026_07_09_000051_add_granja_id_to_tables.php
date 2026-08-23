<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tabelas = [
        'users',
        'produtos',
        'veiculos',
        'clientes',
        'rotas',
        'cargas_caminhao',
        'vendas',
        'retornos_caminhao',
        'conciliacoes',
    ];

    public function up(): void
    {
        foreach ($this->tabelas as $tabela) {
            Schema::table($tabela, function (Blueprint $table) {
                // Nulo apenas para o super admin da plataforma (users)
                // e registros legados; toda a operação pertence a uma granja.
                $table->foreignId('granja_id')->nullable()->constrained('granjas');
            });
        }

        Schema::table('nota_sequencias', function (Blueprint $table) {
            // Numeração de notas é independente por granja (0 = sem granja/testes)
            $table->unsignedBigInteger('granja_id')->default(0);
            $table->dropUnique(['tipo']);
            $table->unique(['tipo', 'granja_id']);
        });
    }

    public function down(): void
    {
        foreach ($this->tabelas as $tabela) {
            Schema::table($tabela, function (Blueprint $table) {
                $table->dropConstrainedForeignId('granja_id');
            });
        }

        Schema::table('nota_sequencias', function (Blueprint $table) {
            $table->dropUnique(['tipo', 'granja_id']);
            $table->dropColumn('granja_id');
            $table->unique(['tipo']);
        });
    }
};
