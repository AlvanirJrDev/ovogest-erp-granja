<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recebimentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('granja_id')->nullable()->constrained('granjas');
            $table->foreignId('venda_id')->constrained('vendas');
            $table->decimal('valor', 10, 2);
            $table->string('forma')->comment('dinheiro, pix ou outro');
            $table->date('data');
            $table->foreignId('recebido_por')->nullable()->constrained('users');
            $table->string('observacao')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recebimentos');
    }
};
