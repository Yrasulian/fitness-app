<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    public function index(Request $request)
    {
        $query = Exercise::query();
        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('muscle_group', 'like', '%' . $request->search . '%');
        }
        if ($request->muscle_group) {
            $query->where('muscle_group', $request->muscle_group);
        }
        return response()->json($query->orderBy('muscle_group')->orderBy('name')->get());
    }

    public function muscleGroups()
    {
        $groups = Exercise::distinct()->pluck('muscle_group')->sort()->values();
        return response()->json($groups);
    }
}
