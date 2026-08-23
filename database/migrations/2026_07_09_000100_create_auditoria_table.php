<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Trilha de auditoria: quem criou, alterou ou excluiu cada registro. */
    public function up(): void
    {
        Schema::create('auditoria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('granja_id')->nullable()->constrained('granjas');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('acao', 12)->comment('criou, atualizou ou excluiu');
            $table->string('modelo');
            $table->unsignedBigInteger('registro_id');
            $table->string('registro_rotulo')->nullable();
            $table->json('alteracoes')->nullable()->comment('campo: {de, para}');
            $table->timestamp('created_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria');
    }
};
