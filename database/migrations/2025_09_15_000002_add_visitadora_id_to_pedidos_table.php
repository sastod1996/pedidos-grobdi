<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            if (!Schema::hasColumn('pedidos', 'visitadora_id')) {
                $table->unsignedBigInteger('visitadora_id')->nullable()->after('user_id');
                $table->foreign('visitadora_id')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            if (Schema::hasColumn('pedidos', 'visitadora_id')) {
                $table->dropForeign(['visitadora_id']);
                $table->dropColumn('visitadora_id');
            }
        });
    }
};