<?php

namespace App\Http\Controllers;

use App\Models\WorkoutSession;
use App\Models\ExerciseLog;
use Illuminate\Http\Request;

class WorkoutController extends Controller
{
    public function startWorkout(Request $request)
    {
        $validated = $request->validate([
            'training_plan_id' => 'nullable|exists:training_plans,id',
            'session_name' => 'required|string',
        ]);

        $workout = WorkoutSession::create([
            'user_id' => auth()->id(),
            'workout_date' => now()->toDateString(),
            'session_name' => $validated['session_name'],
            'training_plan_id' => $validated['training_plan_id'] ?? null,
        ]);

        return response()->json($workout, 201);
    }

    public function logExercise(Request $request, WorkoutSession $workout)
    {
        $this->authorize('update', $workout);

        $validated = $request->validate([
            'exercise_name' => 'required|string',
            'set_number' => 'required|integer',
            'reps' => 'nullable|integer',
            'weight' => 'nullable|numeric',
            'rir' => 'nullable|integer|min:0|max:5',
            'weight_unit' => 'in:kg,lbs',
            'duration_seconds' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        $log = ExerciseLog::create([
            'workout_session_id' => $workout->id,
            ...$validated,
        ]);

        return response()->json($log, 201);
    }

    public function endWorkout(Request $request, WorkoutSession $workout)
    {
        $this->authorize('update', $workout);

        $validated = $request->validate([
            'duration_minutes' => 'required|integer',
            'energy_level' => 'required|integer|min:1|max:10',
            'notes' => 'nullable|string',
        ]);

        $workout->update([
            ...$validated,
            'completed' => true,
        ]);

        return response()->json($workout);
    }

    public function getWorkoutHistory(Request $request)
    {
        $workouts = WorkoutSession::where('user_id', auth()->id())
            ->with('exerciseLogs')
            ->orderBy('workout_date', 'desc')
            ->paginate(20);

        return response()->json($workouts);
    }
}
