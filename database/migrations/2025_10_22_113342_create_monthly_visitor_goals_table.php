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
        Schema::create('monthly_visitor_goals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('goal_not_reached_config_id');
            $table->foreign('goal_not_reached_config_id', 'gnr_config_id_mnthly_vstr_goals_fk')
                ->references('id')->on('goal_not_reached_configs');
            $table->string('tipo_medico');
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_visitor_goals');
    }
};
