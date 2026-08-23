<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('granjas', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable()->after('nome')->comment('Prefixo da granja nas URLs do painel');
        });
    }

    public function down(): void
    {
        Schema::table('granjas', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
