<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carga_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carga_caminhao_id')->constrained('cargas_caminhao')->cascadeOnDelete();
            $table->foreignId('produto_id')->constrained('produtos');
            $table->unsignedInteger('quantidade');
            $table->decimal('valor_unitario', 10, 2)->comment('Preço de venda no momento do carregamento');
            $table->timestamps();

            $table->unique(['carga_caminhao_id', 'produto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carga_itens');
    }
};
