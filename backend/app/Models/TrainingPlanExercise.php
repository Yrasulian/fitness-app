<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingPlanExercise extends Model
{
    protected $fillable = [
        'training_plan_id', 'exercise_name', 'muscle_group',
        'target_sets', 'target_reps', 'target_weight', 'weight_unit',
        'target_rir', 'rest_seconds', 'notes', 'order_index',
    ];
}
