<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NutritionLog extends Model
{
    protected $fillable = [
        'user_id', 'log_date', 'meal_type', 'food_item', 'quantity',
        'unit', 'calories', 'protein', 'carbs', 'fats',
    ];
}
