<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkoutSession extends Model
{
    protected $fillable = [
        'user_id', 'training_plan_id', 'workout_date', 'session_name',
        'duration_minutes', 'energy_level', 'notes', 'completed',
    ];

    public function exerciseLogs()
    {
        return $this->hasMany(ExerciseLog::class, 'workout_session_id');
    }
}
