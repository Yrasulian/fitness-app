<?php

namespace App\Http\Controllers;

use App\Models\TrainingPlan;
use App\Models\TrainingPlanExercise;
use Illuminate\Http\Request;

class TrainingPlanController extends Controller
{
    public function index(Request $request)
    {
        $plans = TrainingPlan::where('user_id', auth()->id())->withCount('exercises')->get();
        return response()->json($plans);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'template_type' => 'required|in:PPL,UL,Full Body,Push/Pull/Legs,Custom',
            'duration_weeks' => 'required|integer|min:1',
        ]);

        $plan = TrainingPlan::create(['user_id' => auth()->id(), ...$validated]);
        return response()->json($plan, 201);
    }

    public function show(TrainingPlan $plan)
    {
        abort_if($plan->user_id !== auth()->id(), 403);
        return response()->json($plan->load('exercises'));
    }

    public function update(Request $request, TrainingPlan $plan)
    {
        abort_if($plan->user_id !== auth()->id(), 403);
        $plan->update($request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'template_type' => 'in:PPL,UL,Full Body,Push/Pull/Legs,Custom',
            'duration_weeks' => 'integer|min:1',
            'status' => 'in:Active,Inactive,Archived',
        ]));
        return response()->json($plan);
    }

    public function destroy(TrainingPlan $plan)
    {
        abort_if($plan->user_id !== auth()->id(), 403);
        $plan->delete();
        return response()->json(['message' => 'Plan deleted']);
    }

    // ── Plan Exercises ─────────────────────────────────────────────

    public function addExercise(Request $request, TrainingPlan $plan)
    {
        abort_if($plan->user_id !== auth()->id(), 403);
        $validated = $request->validate([
            'exercise_name' => 'required|string|max:255',
            'muscle_group'  => 'nullable|string',
            'target_sets'   => 'required|integer|min:1',
            'target_reps'   => 'required|string|max:20',
            'target_weight' => 'nullable|numeric',
            'weight_unit'   => 'in:kg,lbs',
            'target_rir'    => 'nullable|integer|min:0|max:5',
            'rest_seconds'  => 'nullable|integer',
            'notes'         => 'nullable|string',
        ]);
        $maxOrder = $plan->exercises()->max('order_index') ?? -1;
        $ex = TrainingPlanExercise::create([
            'training_plan_id' => $plan->id,
            'order_index' => $maxOrder + 1,
            ...$validated,
        ]);
        return response()->json($ex, 201);
    }

    public function updateExercise(Request $request, TrainingPlan $plan, TrainingPlanExercise $exercise)
    {
        abort_if($plan->user_id !== auth()->id(), 403);
        $exercise->update($request->validate([
            'target_sets'   => 'integer|min:1',
            'target_reps'   => 'string|max:20',
            'target_weight' => 'nullable|numeric',
            'weight_unit'   => 'in:kg,lbs',
            'target_rir'    => 'nullable|integer|min:0|max:5',
            'rest_seconds'  => 'nullable|integer',
            'notes'         => 'nullable|string',
        ]));
        return response()->json($exercise);
    }

    public function removeExercise(TrainingPlan $plan, TrainingPlanExercise $exercise)
    {
        abort_if($plan->user_id !== auth()->id(), 403);
        $exercise->delete();
        return response()->json(['message' => 'Exercise removed']);
    }
}

