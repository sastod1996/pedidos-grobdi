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
        Schema::create('visitor_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('monthly_visitor_goal_id')->constrained('monthly_visitor_goals');
            $table->decimal('goal_amount', 10, 2);
            $table->decimal('commission_percentage', 4, 4);
            $table->decimal('debited_amount', 10, 2)->nullable();
            $table->datetime('debited_datetime')->nullable();
            $table->text('debit_comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_goals');
    }
};
