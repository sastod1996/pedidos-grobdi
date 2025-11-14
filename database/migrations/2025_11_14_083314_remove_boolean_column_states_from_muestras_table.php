<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('muestras', function (Blueprint $table) {
            if (Schema::hasColumn('muestras', 'lab_state')) {
                $table->dropColumn('lab_state');
            }
            if (Schema::hasColumn('muestras', 'aprobado_jefe_comercial')) {
                $table->dropColumn('aprobado_jefe_comercial');
            }
            if (Schema::hasColumn('muestras', 'aprobado_coordinadora')) {
                $table->dropColumn('aprobado_coordinadora');
            }
            if (Schema::hasColumn('muestras', 'aprobado_jefe_operaciones')) {
                $table->dropColumn('aprobado_jefe_operaciones');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('muestras', function (Blueprint $table) {
            $table->boolean('lab_state')->default(false);
            $table->boolean('aprobado_jefe_comercial')->default(false);
            $table->boolean('aprobado_coordinadora')->default(false);
            $table->boolean('aprobado_jefe_operaciones')->default(false);
        });
    }
};
