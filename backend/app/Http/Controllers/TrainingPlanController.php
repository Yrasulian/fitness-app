<?php

namespace App\Http\Controllers;

use App\Models\TrainingPlan;
use Illuminate\Http\Request;

class TrainingPlanController extends Controller
{
    public function index(Request $request)
    {
        $plans = TrainingPlan::where('user_id', auth()->id())->get();
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

        $plan = TrainingPlan::create([
            'user_id' => auth()->id(),
            ...$validated,
        ]);

        return response()->json($plan, 201);
    }

    public function show(TrainingPlan $plan)
    {
        $this->authorize('view', $plan);
        return response()->json($plan);
    }

    public function update(Request $request, TrainingPlan $plan)
    {
        $this->authorize('update', $plan);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'template_type' => 'in:PPL,UL,Full Body,Push/Pull/Legs,Custom',
            'duration_weeks' => 'integer|min:1',
            'status' => 'in:Active,Inactive,Archived',
        ]);

        $plan->update($validated);

        return response()->json($plan);
    }

    public function destroy(TrainingPlan $plan)
    {
        $this->authorize('delete', $plan);
        $plan->delete();

        return response()->json(['message' => 'Plan deleted']);
    }
}
