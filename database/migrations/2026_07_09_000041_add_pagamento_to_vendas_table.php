<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendas', function (Blueprint $table) {
            $table->foreignId('vendedor_id')->nullable()->after('cliente_id')->constrained('users');
            $table->decimal('valor_pago', 10, 2)->default(0)->after('forma_pagamento');
            $table->date('data_vencimento')->nullable()->after('valor_pago')->comment('Para vendas a prazo');
        });
    }

    public function down(): void
    {
        Schema::table('vendas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vendedor_id');
            $table->dropColumn(['valor_pago', 'data_vencimento']);
        });
    }
};
