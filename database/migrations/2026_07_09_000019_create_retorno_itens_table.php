<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retorno_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retorno_caminhao_id')->constrained('retornos_caminhao')->cascadeOnDelete();
            $table->foreignId('produto_id')->constrained('produtos');
            $table->unsignedInteger('quantidade');
            $table->string('motivo')->comment('sobra, quebra ou devolucao');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retorno_itens');
    }
};
