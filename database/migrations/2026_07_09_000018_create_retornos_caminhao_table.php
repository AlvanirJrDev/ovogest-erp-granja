<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retornos_caminhao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carga_caminhao_id')->unique()->constrained('cargas_caminhao');
            $table->unsignedBigInteger('numero')->unique();
            $table->dateTime('data_hora_retorno');
            $table->string('status')->default('aberto');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retornos_caminhao');
    }
};
