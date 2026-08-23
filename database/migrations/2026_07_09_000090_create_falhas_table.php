<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Erros do sistema, deduplicados por assinatura, para o painel da plataforma. */
    public function up(): void
    {
        Schema::create('falhas', function (Blueprint $table) {
            $table->id();
            $table->string('hash', 40)->unique()->comment('Assinatura exceção+arquivo+linha');
            $table->string('excecao');
            $table->text('mensagem');
            $table->string('arquivo')->nullable();
            $table->string('url')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->unsignedInteger('ocorrencias')->default(1);
            $table->timestamp('primeira_em');
            $table->timestamp('ultima_em')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('falhas');
    }
};
