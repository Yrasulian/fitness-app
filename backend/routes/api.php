<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TrainingPlanController;
use App\Http\Controllers\WorkoutController;
use App\Http\Controllers\NutritionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Training Plans
    Route::get('/training-plans', [TrainingPlanController::class, 'index']);
    Route::post('/training-plans', [TrainingPlanController::class, 'store']);
    Route::get('/training-plans/{plan}', [TrainingPlanController::class, 'show']);
    Route::put('/training-plans/{plan}', [TrainingPlanController::class, 'update']);
    Route::delete('/training-plans/{plan}', [TrainingPlanController::class, 'destroy']);

    // Workouts
    Route::post('/workouts/start', [WorkoutController::class, 'startWorkout']);
    Route::post('/workouts/{workout}/exercise', [WorkoutController::class, 'logExercise']);
    Route::post('/workouts/{workout}/end', [WorkoutController::class, 'endWorkout']);
    Route::get('/workouts/history', [WorkoutController::class, 'getWorkoutHistory']);

    // Nutrition
    Route::post('/nutrition/log', [NutritionController::class, 'logMeal']);
    Route::get('/nutrition/{date}', [NutritionController::class, 'getDailyNutrition']);
    Route::delete('/nutrition/{log}', [NutritionController::class, 'deleteMeal']);
});
