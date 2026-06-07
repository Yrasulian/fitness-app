<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExerciseLog extends Model
{
    protected $fillable = [
        'workout_session_id', 'exercise_name', 'set_number', 'reps',
        'weight', 'rir', 'weight_unit', 'duration_seconds', 'notes',
    ];
}
