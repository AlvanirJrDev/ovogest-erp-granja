<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conciliacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carga_caminhao_id')->unique()->constrained('cargas_caminhao');
            $table->unsignedInteger('total_saiu');
            $table->unsignedInteger('total_vendido');
            $table->unsignedInteger('total_retornou');
            $table->integer('diferenca')->comment('saiu - vendido - retornou');
            $table->string('status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conciliacoes');
    }
};
