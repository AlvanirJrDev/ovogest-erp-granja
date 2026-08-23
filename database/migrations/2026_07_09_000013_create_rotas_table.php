<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rotas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->foreignId('veiculo_id')->constrained('veiculos');
            $table->date('data');
            $table->foreignId('responsavel_id')->constrained('users');
            $table->string('status')->default('planejada');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rotas');
    }
};
