<?php

namespace App\Http\Controllers;

use App\Models\NutritionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NutritionController extends Controller
{
    public function logMeal(Request $request)
    {
        $validated = $request->validate([
            'log_date' => 'required|date',
            'meal_type' => 'required|in:Breakfast,Lunch,Dinner,Snack,Post-Workout',
            'food_item' => 'required|string',
            'quantity' => 'required|numeric',
            'unit' => 'required|string',
            'calories' => 'required|integer',
            'protein' => 'nullable|numeric',
            'carbs' => 'nullable|numeric',
            'fats' => 'nullable|numeric',
        ]);

        $log = NutritionLog::create([
            'user_id' => auth()->id(),
            ...$validated,
        ]);

        return response()->json($log, 201);
    }

    public function getDailyNutrition(Request $request, $date)
    {
        $logs = NutritionLog::where('user_id', auth()->id())
            ->where('log_date', $date)
            ->get();

        $totals = $logs->reduce(function ($carry, $item) {
            return [
                'calories' => ($carry['calories'] ?? 0) + $item->calories,
                'protein' => ($carry['protein'] ?? 0) + $item->protein,
                'carbs' => ($carry['carbs'] ?? 0) + $item->carbs,
                'fats' => ($carry['fats'] ?? 0) + $item->fats,
            ];
        }, []);

        return response()->json([
            'date' => $date,
            'meals' => $logs,
            'totals' => $totals,
        ]);
    }

    public function deleteMeal(NutritionLog $log)
    {
        $this->authorize('delete', $log);
        $log->delete();

        return response()->json(['message' => 'Meal deleted']);
    }
}
