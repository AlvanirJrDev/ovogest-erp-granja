<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('documento')->nullable()->comment('CPF ou CNPJ');
            $table->string('telefone')->nullable();
            $table->string('endereco')->nullable();
            // Reservado para tabelas de preço negociadas por cliente (fase futura)
            $table->unsignedBigInteger('tabela_preco_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
