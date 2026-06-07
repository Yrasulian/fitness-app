<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingPlan extends Model
{
    protected $fillable = [
        'user_id', 'name', 'description', 'template_type', 'duration_weeks', 'status',
    ];

    public function exercises()
    {
        return $this->hasMany(TrainingPlanExercise::class)->orderBy('order_index');
    }
}
