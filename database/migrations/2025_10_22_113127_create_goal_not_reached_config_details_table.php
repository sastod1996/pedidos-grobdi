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
        Schema::create('goal_not_reached_config_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('goal_not_reached_config_id');
            $table->foreign('goal_not_reached_config_id', 'gnr_config_id_gnr_details_fk')
                ->references('id')->on('goal_not_reached_configs')->onDelete('cascade');
            $table->decimal('initial_percentage', 4, 4)->default(0);
            $table->decimal('final_percentage', 4, 4)->default(0);
            $table->decimal('commission', 4, 4)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goal_not_reached_config_details');
    }
};
