<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Trilha de auditoria de logins: quem entrou, quando e de onde. */
    public function up(): void
    {
        Schema::create('acessos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('logado_em');

            $table->index(['user_id', 'logado_em']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acessos');
    }
};
