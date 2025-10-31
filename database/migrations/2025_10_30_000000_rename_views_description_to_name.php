<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1) Add the new `name` column (nullable for now)
        Schema::table('views', function (Blueprint $table) {
            $table->string('name')->nullable()->after('is_menu');
        });

        // 2) Copy existing data from `description` to `name`
        DB::statement('UPDATE `views` SET `name` = `description`');

        // 3) Remove old `description` column and add the new nullable `description` column
        Schema::table('views', function (Blueprint $table) {
            $table->dropColumn('description');
        });

        Schema::table('views', function (Blueprint $table) {
            $table->string('description')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore previous state: move name back to description and drop name
        Schema::table('views', function (Blueprint $table) {
            // ensure description exists to receive values
            if (!Schema::hasColumn('views', 'description')) {
                $table->string('description')->nullable()->after('is_menu');
            }
        });

        // copy name back to description
        DB::statement('UPDATE `views` SET `description` = `name`');

        Schema::table('views', function (Blueprint $table) {
            if (Schema::hasColumn('views', 'name')) {
                $table->dropColumn('name');
            }
        });
    }
};
