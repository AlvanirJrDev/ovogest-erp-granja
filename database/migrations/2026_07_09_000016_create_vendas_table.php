<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carga_caminhao_id')->constrained('cargas_caminhao');
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->unsignedBigInteger('numero')->unique();
            $table->dateTime('data_hora');
            $table->string('forma_pagamento');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendas');
    }
};
