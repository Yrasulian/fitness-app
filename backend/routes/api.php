<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TrainingPlanController;
use App\Http\Controllers\WorkoutController;
use App\Http\Controllers\NutritionController;
use App\Http\Controllers\ExerciseController;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Password reset
Route::post('/auth/forgot-password', function (\Illuminate\Http\Request $req) {
    $req->validate(['email' => 'required|email']);
    $user = \App\Models\User::where('email', $req->email)->first();
    $response = ['message' => 'If an account with this email exists, a reset link has been sent.'];
    if (!$user) return response()->json($response);

    $plainToken = \Illuminate\Support\Str::random(64);
    $hashed = hash('sha256', $plainToken);
    \Illuminate\Support\Facades\DB::table('password_reset_tokens')
        ->updateOrInsert(['email' => $req->email], ['token' => $hashed, 'created_at' => now()]);

    $frontendUrl = env('FRONTEND_URL', 'http://localhost:3001');
    $resetLink = $frontendUrl . '/reset-password?token=' . $plainToken . '&email=' . urlencode($req->email);

    // Send email if mail is configured
    try {
        \Illuminate\Support\Facades\Mail::send([], [], function ($mail) use ($user, $resetLink) {
            $mail->to($user->email)
                ->subject('Password Reset – MyFitness')
                ->setBody('<p>Hi ' . e($user->name) . ',</p><p>Click to reset your password:</p><p><a href="' . $resetLink . '">' . $resetLink . '</a></p><p>This link expires in 60 minutes.</p>', 'text/html');
        });
    } catch (\Exception $e) {
        // Mail not configured — ignore in dev
    }

    // In local dev, return the reset link directly for easy testing
    if (env('APP_ENV') === 'local') {
        $response['dev_reset_link'] = $resetLink;
    }
    return response()->json($response);
});

Route::post('/auth/reset-password', function (\Illuminate\Http\Request $req) {
    $req->validate([
        'email' => 'required|email',
        'token' => 'required|string',
        'password' => 'required|string|min:8|confirmed',
    ]);
    $record = \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $req->email)->first();
    if (!$record || hash('sha256', $req->token) !== $record->token) {
        return response()->json(['message' => 'Invalid or expired reset token.'], 422);
    }
    // Check 60 min expiry
    if (\Carbon\Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $req->email)->delete();
        return response()->json(['message' => 'Reset token has expired. Please request a new one.'], 422);
    }
    $user = \App\Models\User::where('email', $req->email)->first();
    if (!$user) return response()->json(['message' => 'User not found.'], 404);

    $user->password = \Illuminate\Support\Facades\Hash::make($req->password);
    $user->save();
    \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $req->email)->delete();

    return response()->json(['message' => 'Password reset successfully. You can now log in.']);
});

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

    // Exercises — static list, searchable
    Route::get('/exercises', function (\Illuminate\Http\Request $req) {
        $exercises = [
            ['name'=>'Bench Press','muscle_group'=>'Chest','equipment'=>'Barbell'],
            ['name'=>'Incline Bench Press','muscle_group'=>'Chest','equipment'=>'Barbell'],
            ['name'=>'Decline Bench Press','muscle_group'=>'Chest','equipment'=>'Barbell'],
            ['name'=>'Dumbbell Bench Press','muscle_group'=>'Chest','equipment'=>'Dumbbell'],
            ['name'=>'Incline Dumbbell Press','muscle_group'=>'Chest','equipment'=>'Dumbbell'],
            ['name'=>'Dumbbell Fly','muscle_group'=>'Chest','equipment'=>'Dumbbell'],
            ['name'=>'Cable Crossover','muscle_group'=>'Chest','equipment'=>'Cable'],
            ['name'=>'Pec Deck','muscle_group'=>'Chest','equipment'=>'Machine'],
            ['name'=>'Machine Chest Press','muscle_group'=>'Chest','equipment'=>'Machine'],
            ['name'=>'Push-Up','muscle_group'=>'Chest','equipment'=>'Bodyweight'],
            ['name'=>'Wide Push-Up','muscle_group'=>'Chest','equipment'=>'Bodyweight'],
            ['name'=>'Diamond Push-Up','muscle_group'=>'Chest','equipment'=>'Bodyweight'],
            ['name'=>'Chest Dip','muscle_group'=>'Chest','equipment'=>'Bodyweight'],
            ['name'=>'Pull-Up','muscle_group'=>'Back','equipment'=>'Bodyweight'],
            ['name'=>'Chin-Up','muscle_group'=>'Back','equipment'=>'Bodyweight'],
            ['name'=>'Wide Grip Pull-Up','muscle_group'=>'Back','equipment'=>'Bodyweight'],
            ['name'=>'Lat Pulldown','muscle_group'=>'Back','equipment'=>'Cable'],
            ['name'=>'Wide Grip Lat Pulldown','muscle_group'=>'Back','equipment'=>'Cable'],
            ['name'=>'Close Grip Lat Pulldown','muscle_group'=>'Back','equipment'=>'Cable'],
            ['name'=>'Seated Cable Row','muscle_group'=>'Back','equipment'=>'Cable'],
            ['name'=>'Bent Over Barbell Row','muscle_group'=>'Back','equipment'=>'Barbell'],
            ['name'=>'Bent Over Dumbbell Row','muscle_group'=>'Back','equipment'=>'Dumbbell'],
            ['name'=>'One Arm Dumbbell Row','muscle_group'=>'Back','equipment'=>'Dumbbell'],
            ['name'=>'T-Bar Row','muscle_group'=>'Back','equipment'=>'Barbell'],
            ['name'=>'Face Pull','muscle_group'=>'Back','equipment'=>'Cable'],
            ['name'=>'Straight Arm Pulldown','muscle_group'=>'Back','equipment'=>'Cable'],
            ['name'=>'Deadlift','muscle_group'=>'Back','equipment'=>'Barbell'],
            ['name'=>'Romanian Deadlift','muscle_group'=>'Back','equipment'=>'Barbell'],
            ['name'=>'Rack Pull','muscle_group'=>'Back','equipment'=>'Barbell'],
            ['name'=>'Good Morning','muscle_group'=>'Back','equipment'=>'Barbell'],
            ['name'=>'Hyperextension','muscle_group'=>'Back','equipment'=>'Machine'],
            ['name'=>'Barbell Shrug','muscle_group'=>'Back','equipment'=>'Barbell'],
            ['name'=>'Inverted Row','muscle_group'=>'Back','equipment'=>'Bodyweight'],
            ['name'=>'Overhead Press (Barbell)','muscle_group'=>'Shoulders','equipment'=>'Barbell'],
            ['name'=>'Overhead Press (Dumbbell)','muscle_group'=>'Shoulders','equipment'=>'Dumbbell'],
            ['name'=>'Arnold Press','muscle_group'=>'Shoulders','equipment'=>'Dumbbell'],
            ['name'=>'Seated Dumbbell Press','muscle_group'=>'Shoulders','equipment'=>'Dumbbell'],
            ['name'=>'Military Press','muscle_group'=>'Shoulders','equipment'=>'Barbell'],
            ['name'=>'Machine Shoulder Press','muscle_group'=>'Shoulders','equipment'=>'Machine'],
            ['name'=>'Lateral Raise','muscle_group'=>'Shoulders','equipment'=>'Dumbbell'],
            ['name'=>'Cable Lateral Raise','muscle_group'=>'Shoulders','equipment'=>'Cable'],
            ['name'=>'Machine Lateral Raise','muscle_group'=>'Shoulders','equipment'=>'Machine'],
            ['name'=>'Front Raise','muscle_group'=>'Shoulders','equipment'=>'Dumbbell'],
            ['name'=>'Rear Delt Fly','muscle_group'=>'Shoulders','equipment'=>'Dumbbell'],
            ['name'=>'Cable Rear Delt Fly','muscle_group'=>'Shoulders','equipment'=>'Cable'],
            ['name'=>'Reverse Pec Deck','muscle_group'=>'Shoulders','equipment'=>'Machine'],
            ['name'=>'Upright Row','muscle_group'=>'Shoulders','equipment'=>'Barbell'],
            ['name'=>'Barbell Curl','muscle_group'=>'Biceps','equipment'=>'Barbell'],
            ['name'=>'EZ Bar Curl','muscle_group'=>'Biceps','equipment'=>'EZ Bar'],
            ['name'=>'Dumbbell Curl','muscle_group'=>'Biceps','equipment'=>'Dumbbell'],
            ['name'=>'Hammer Curl','muscle_group'=>'Biceps','equipment'=>'Dumbbell'],
            ['name'=>'Incline Dumbbell Curl','muscle_group'=>'Biceps','equipment'=>'Dumbbell'],
            ['name'=>'Concentration Curl','muscle_group'=>'Biceps','equipment'=>'Dumbbell'],
            ['name'=>'Preacher Curl','muscle_group'=>'Biceps','equipment'=>'EZ Bar'],
            ['name'=>'Cable Curl','muscle_group'=>'Biceps','equipment'=>'Cable'],
            ['name'=>'Reverse Curl','muscle_group'=>'Biceps','equipment'=>'Barbell'],
            ['name'=>'Zottman Curl','muscle_group'=>'Biceps','equipment'=>'Dumbbell'],
            ['name'=>'Machine Curl','muscle_group'=>'Biceps','equipment'=>'Machine'],
            ['name'=>'Tricep Pushdown (Rope)','muscle_group'=>'Triceps','equipment'=>'Cable'],
            ['name'=>'Tricep Pushdown (Bar)','muscle_group'=>'Triceps','equipment'=>'Cable'],
            ['name'=>'Skull Crusher','muscle_group'=>'Triceps','equipment'=>'Barbell'],
            ['name'=>'EZ Bar Skull Crusher','muscle_group'=>'Triceps','equipment'=>'EZ Bar'],
            ['name'=>'Close Grip Bench Press','muscle_group'=>'Triceps','equipment'=>'Barbell'],
            ['name'=>'Overhead Tricep Extension','muscle_group'=>'Triceps','equipment'=>'Dumbbell'],
            ['name'=>'Tricep Dip','muscle_group'=>'Triceps','equipment'=>'Bodyweight'],
            ['name'=>'Tricep Kickback','muscle_group'=>'Triceps','equipment'=>'Dumbbell'],
            ['name'=>'Back Squat','muscle_group'=>'Legs','equipment'=>'Barbell'],
            ['name'=>'Front Squat','muscle_group'=>'Legs','equipment'=>'Barbell'],
            ['name'=>'Goblet Squat','muscle_group'=>'Legs','equipment'=>'Dumbbell'],
            ['name'=>'Hack Squat','muscle_group'=>'Legs','equipment'=>'Machine'],
            ['name'=>'Leg Press','muscle_group'=>'Legs','equipment'=>'Machine'],
            ['name'=>'Bulgarian Split Squat','muscle_group'=>'Legs','equipment'=>'Dumbbell'],
            ['name'=>'Lunge','muscle_group'=>'Legs','equipment'=>'Bodyweight'],
            ['name'=>'Walking Lunge','muscle_group'=>'Legs','equipment'=>'Dumbbell'],
            ['name'=>'Leg Extension','muscle_group'=>'Legs','equipment'=>'Machine'],
            ['name'=>'Leg Curl (Lying)','muscle_group'=>'Legs','equipment'=>'Machine'],
            ['name'=>'Leg Curl (Seated)','muscle_group'=>'Legs','equipment'=>'Machine'],
            ['name'=>'Romanian Deadlift (Dumbbell)','muscle_group'=>'Legs','equipment'=>'Dumbbell'],
            ['name'=>'Stiff Leg Deadlift','muscle_group'=>'Legs','equipment'=>'Barbell'],
            ['name'=>'Step Up','muscle_group'=>'Legs','equipment'=>'Dumbbell'],
            ['name'=>'Pistol Squat','muscle_group'=>'Legs','equipment'=>'Bodyweight'],
            ['name'=>'Hip Thrust (Barbell)','muscle_group'=>'Glutes','equipment'=>'Barbell'],
            ['name'=>'Hip Thrust (Machine)','muscle_group'=>'Glutes','equipment'=>'Machine'],
            ['name'=>'Glute Bridge','muscle_group'=>'Glutes','equipment'=>'Bodyweight'],
            ['name'=>'Cable Kickback','muscle_group'=>'Glutes','equipment'=>'Cable'],
            ['name'=>'Hip Abduction (Machine)','muscle_group'=>'Glutes','equipment'=>'Machine'],
            ['name'=>'Standing Calf Raise','muscle_group'=>'Calves','equipment'=>'Machine'],
            ['name'=>'Seated Calf Raise','muscle_group'=>'Calves','equipment'=>'Machine'],
            ['name'=>'Single Leg Calf Raise','muscle_group'=>'Calves','equipment'=>'Bodyweight'],
            ['name'=>'Plank','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            ['name'=>'Side Plank','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            ['name'=>'Crunch','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            ['name'=>'Bicycle Crunch','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            ['name'=>'Russian Twist','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            ['name'=>'Leg Raise','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            ['name'=>'Hanging Leg Raise','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            ['name'=>'Ab Rollout','muscle_group'=>'Core','equipment'=>'Ab Wheel'],
            ['name'=>'Cable Crunch','muscle_group'=>'Core','equipment'=>'Cable'],
            ['name'=>'Mountain Climber','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            ['name'=>'Dead Bug','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            ['name'=>'Dragon Flag','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            ['name'=>'Running','muscle_group'=>'Cardio','equipment'=>'None'],
            ['name'=>'Cycling','muscle_group'=>'Cardio','equipment'=>'Bike'],
            ['name'=>'Rowing Machine','muscle_group'=>'Cardio','equipment'=>'Machine'],
            ['name'=>'Jump Rope','muscle_group'=>'Cardio','equipment'=>'Jump Rope'],
            ['name'=>'Battle Ropes','muscle_group'=>'Cardio','equipment'=>'Battle Ropes'],
            ['name'=>'Burpee','muscle_group'=>'Cardio','equipment'=>'Bodyweight'],
            ['name'=>'Kettlebell Swing','muscle_group'=>'Full Body','equipment'=>'Kettlebell'],
            ['name'=>'Farmers Carry','muscle_group'=>'Full Body','equipment'=>'Dumbbell'],
            ['name'=>'Thruster','muscle_group'=>'Full Body','equipment'=>'Barbell'],
            ['name'=>'Clean and Press','muscle_group'=>'Full Body','equipment'=>'Barbell'],
        ];
        $search = strtolower($req->query('search', ''));
        $muscle = $req->query('muscle', '');
        $list = collect($exercises);
        if ($search) $list = $list->filter(fn($e) => str_contains(strtolower($e['name']), $search) || str_contains(strtolower($e['muscle_group']), $search));
        if ($muscle) $list = $list->filter(fn($e) => strtolower($e['muscle_group']) === strtolower($muscle));
        // Merge with admin-added custom exercises
        $custom = \Illuminate\Support\Facades\DB::table('custom_exercises')->get()->map(fn($e) => ['name'=>$e->name,'muscle_group'=>$e->muscle_group,'equipment'=>$e->equipment,'custom'=>true])->toArray();
        if ($custom) {
            $customCol = collect($custom);
            if ($search) $customCol = $customCol->filter(fn($e) => str_contains(strtolower($e['name']), $search) || str_contains(strtolower($e['muscle_group']), $search));
            if ($muscle) $customCol = $customCol->filter(fn($e) => strtolower($e['muscle_group']) === strtolower($muscle));
            $list = $list->concat($customCol);
        }
        return response()->json($list->values());
    });

    // Admin routes
    Route::get('/admin/users', function () {
        abort_if(!auth()->user()->is_admin, 403);
        return response()->json(\Illuminate\Support\Facades\DB::table('users')
            ->select('id','name','email','is_admin','created_at')->orderBy('id')->get());
    });
    Route::put('/admin/users/{id}/toggle-admin', function (int $id) {
        abort_if(!auth()->user()->is_admin, 403);
        $user = \Illuminate\Support\Facades\DB::table('users')->find($id);
        abort_if(!$user, 404);
        \Illuminate\Support\Facades\DB::table('users')->where('id',$id)->update(['is_admin' => $user->is_admin ? 0 : 1]);
        return response()->json(['message' => 'Updated']);
    });
    Route::get('/admin/exercises', function () {
        abort_if(!auth()->user()->is_admin, 403);
        return response()->json(\Illuminate\Support\Facades\DB::table('custom_exercises')->orderBy('name')->get());
    });
    Route::post('/admin/exercises', function (\Illuminate\Http\Request $req) {
        abort_if(!auth()->user()->is_admin, 403);
        $v = $req->validate([
            'name'=>'required|string|max:255',
            'muscle_group'=>'required|string',
            'equipment'=>'nullable|string',
            'instructions'=>'nullable|string',
        ]);
        $id = \Illuminate\Support\Facades\DB::table('custom_exercises')->insertGetId(array_merge($v,['created_at'=>now(),'updated_at'=>now()]));
        return response()->json(\Illuminate\Support\Facades\DB::table('custom_exercises')->find($id), 201);
    });
    Route::delete('/admin/exercises/{id}', function (int $id) {
        abort_if(!auth()->user()->is_admin, 403);
        \Illuminate\Support\Facades\DB::table('custom_exercises')->where('id',$id)->delete();
        return response()->json(['message' => 'Deleted']);
    });

    // Training Plan Exercises
    Route::get('/training-plans/{plan}/exercises', function (\App\Models\TrainingPlan $plan) {
        abort_if($plan->user_id !== auth()->id(), 403);
        return response()->json(\Illuminate\Support\Facades\DB::table('training_plan_exercises')
            ->where('training_plan_id', $plan->id)->orderBy('order_index')->get());
    });
    Route::post('/training-plans/{plan}/exercises', function (\Illuminate\Http\Request $req, \App\Models\TrainingPlan $plan) {
        abort_if($plan->user_id !== auth()->id(), 403);
        $v = $req->validate([
            'exercise_name'=>'required|string|max:255','muscle_group'=>'nullable|string',
            'target_sets'=>'required|integer|min:1','target_reps'=>'required|string',
            'target_weight'=>'nullable|numeric','weight_unit'=>'in:kg,lbs',
            'target_rir'=>'nullable|integer|min:0|max:5','rest_seconds'=>'nullable|integer','notes'=>'nullable|string',
        ]);
        $maxOrder = \Illuminate\Support\Facades\DB::table('training_plan_exercises')->where('training_plan_id',$plan->id)->max('order_index') ?? -1;
        $id = \Illuminate\Support\Facades\DB::table('training_plan_exercises')->insertGetId(array_merge($v,[
            'training_plan_id'=>$plan->id,'order_index'=>$maxOrder+1,'created_at'=>now(),'updated_at'=>now()
        ]));
        return response()->json(\Illuminate\Support\Facades\DB::table('training_plan_exercises')->find($id), 201);
    });
    Route::put('/training-plans/{plan}/exercises/{exerciseId}', function (\Illuminate\Http\Request $req, \App\Models\TrainingPlan $plan, int $exerciseId) {
        abort_if($plan->user_id !== auth()->id(), 403);
        $v = $req->validate([
            'target_sets'=>'integer|min:1','target_reps'=>'string',
            'target_weight'=>'nullable|numeric','weight_unit'=>'in:kg,lbs',
            'target_rir'=>'nullable|integer|min:0|max:5','rest_seconds'=>'nullable|integer','notes'=>'nullable|string',
        ]);
        \Illuminate\Support\Facades\DB::table('training_plan_exercises')
            ->where('id',$exerciseId)->where('training_plan_id',$plan->id)
            ->update(array_merge($v,['updated_at'=>now()]));
        return response()->json(\Illuminate\Support\Facades\DB::table('training_plan_exercises')->find($exerciseId));
    });
    Route::delete('/training-plans/{plan}/exercises/{exerciseId}', function (\App\Models\TrainingPlan $plan, int $exerciseId) {
        abort_if($plan->user_id !== auth()->id(), 403);
        \Illuminate\Support\Facades\DB::table('training_plan_exercises')
            ->where('id',$exerciseId)->where('training_plan_id',$plan->id)->delete();
        return response()->json(['message'=>'Exercise removed']);
    });
});

// One-time DB setup — visit once, then ignore
Route::post('/exercises/setup', function () {
    if (!Schema::hasTable('training_plan_exercises') || !\Illuminate\Support\Facades\Schema::hasColumn('training_plan_exercises', 'exercise_name')) {
        if (\Illuminate\Support\Facades\Schema::hasTable('training_plan_exercises')) {
            \Illuminate\Support\Facades\Schema::drop('training_plan_exercises');
        }
        \Illuminate\Support\Facades\Schema::create('training_plan_exercises', function ($table) {
            $table->id();
            $table->foreignId('training_plan_id')->constrained()->cascadeOnDelete();
            $table->string('exercise_name');
            $table->string('muscle_group')->nullable();
            $table->integer('target_sets')->default(3);
            $table->string('target_reps')->default('10');
            $table->decimal('target_weight', 8, 2)->nullable();
            $table->enum('weight_unit', ['kg', 'lbs'])->default('kg');
            $table->integer('target_rir')->nullable();
            $table->integer('rest_seconds')->nullable();
            $table->text('notes')->nullable();
            $table->integer('order_index')->default(0);
            $table->timestamps();
        });
    }
    return response()->json(['message' => 'Table ready']);
});
