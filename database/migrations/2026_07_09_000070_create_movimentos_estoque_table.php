<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimentos_estoque', function (Blueprint $table) {
            $table->id();
            $table->foreignId('granja_id')->nullable()->constrained('granjas');
            $table->foreignId('produto_id')->constrained('produtos');
            $table->string('tipo')->comment('producao, carga, retorno ou ajuste');
            $table->integer('quantidade')->comment('positivo entra, negativo sai');
            $table->date('data');
            $table->foreignId('carga_caminhao_id')->nullable()->constrained('cargas_caminhao');
            $table->foreignId('retorno_caminhao_id')->nullable()->constrained('retornos_caminhao');
            $table->foreignId('usuario_id')->nullable()->constrained('users');
            $table->string('observacao')->nullable();
            $table->timestamps();

            $table->index(['granja_id', 'produto_id']);
            $table->index(['granja_id', 'tipo', 'data']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimentos_estoque');
    }
};
