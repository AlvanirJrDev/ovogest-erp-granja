<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cargas_caminhao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rota_id')->constrained('rotas');
            $table->unsignedBigInteger('numero')->unique();
            $table->dateTime('data_hora_saida');
            $table->string('status')->default('aberta');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cargas_caminhao');
    }
};
