<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('training_plan_exercises', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('training_plan_id');
            $table->foreign('training_plan_id')->references('id')->on('training_plans')->onDelete('cascade');
            $table->string('exercise_name');
            $table->string('muscle_group')->nullable();
            $table->integer('target_sets')->default(3);
            $table->string('target_reps')->default('8-12');
            $table->decimal('target_weight', 8, 2)->nullable();
            $table->enum('weight_unit', ['kg', 'lbs'])->default('kg');
            $table->integer('target_rir')->nullable();
            $table->integer('rest_seconds')->default(90);
            $table->text('notes')->nullable();
            $table->integer('order_index')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_plan_exercises');
    }
};
