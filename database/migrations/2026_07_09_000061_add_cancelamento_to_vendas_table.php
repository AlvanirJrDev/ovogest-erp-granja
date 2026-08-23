<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendas', function (Blueprint $table) {
            $table->timestamp('cancelada_em')->nullable();
            $table->foreignId('cancelada_por')->nullable()->constrained('users');
            $table->string('motivo_cancelamento')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('vendas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cancelada_por');
            $table->dropColumn(['cancelada_em', 'motivo_cancelamento']);
        });
    }
};
