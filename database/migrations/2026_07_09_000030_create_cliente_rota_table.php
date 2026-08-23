<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cliente_rota', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rota_id')->constrained('rotas')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['rota_id', 'cliente_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente_rota');
    }
};
