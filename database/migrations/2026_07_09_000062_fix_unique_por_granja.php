<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Numeração de notas e placa de veículo são únicas POR GRANJA,
     * não globais — sem isto, a segunda granja não conseguiria criar
     * a nota nº 1 nem cadastrar uma placa já usada em outra granja.
     */
    public function up(): void
    {
        foreach (['cargas_caminhao', 'vendas', 'retornos_caminhao'] as $tabela) {
            Schema::table($tabela, function (Blueprint $table) {
                $table->dropUnique(['numero']);
                $table->unique(['granja_id', 'numero']);
            });
        }

        Schema::table('veiculos', function (Blueprint $table) {
            $table->dropUnique(['placa']);
            $table->unique(['granja_id', 'placa']);
        });
    }

    public function down(): void
    {
        foreach (['cargas_caminhao', 'vendas', 'retornos_caminhao'] as $tabela) {
            Schema::table($tabela, function (Blueprint $table) {
                $table->dropUnique(['granja_id', 'numero']);
                $table->unique(['numero']);
            });
        }

        Schema::table('veiculos', function (Blueprint $table) {
            $table->dropUnique(['granja_id', 'placa']);
            $table->unique(['placa']);
        });
    }
};
