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
            // ── BRUST ──────────────────────────────────────────────────────────
            ['name'=>'Bankdrücken LH','muscle_group'=>'Chest','equipment'=>'Barbell'],
            ['name'=>'Bankdrücken KH','muscle_group'=>'Chest','equipment'=>'Dumbbell'],
            ['name'=>'Bankdrücken Maschine','muscle_group'=>'Chest','equipment'=>'Machine'],
            ['name'=>'Brustpresse Maschine flach','muscle_group'=>'Chest','equipment'=>'Machine'],
            ['name'=>'Schrägbank LH-Press','muscle_group'=>'Chest','equipment'=>'Barbell'],
            ['name'=>'Schrägbank KH-Press','muscle_group'=>'Chest','equipment'=>'Dumbbell'],
            ['name'=>'Schrägbank Multipresse','muscle_group'=>'Chest','equipment'=>'Machine'],
            ['name'=>'Schrägbank Brustpresse Maschine','muscle_group'=>'Chest','equipment'=>'Machine'],
            ['name'=>'Flachbank LH-Press','muscle_group'=>'Chest','equipment'=>'Barbell'],
            ['name'=>'Negativbank KH-Press','muscle_group'=>'Chest','equipment'=>'Dumbbell'],
            ['name'=>'Negativbank LH-Press','muscle_group'=>'Chest','equipment'=>'Barbell'],
            ['name'=>'KH-Flys flach','muscle_group'=>'Chest','equipment'=>'Dumbbell'],
            ['name'=>'KH-Flys Schrägbank','muscle_group'=>'Chest','equipment'=>'Dumbbell'],
            ['name'=>'Kabel-Flys','muscle_group'=>'Chest','equipment'=>'Cable'],
            ['name'=>'Kabel-Flys von oben','muscle_group'=>'Chest','equipment'=>'Cable'],
            ['name'=>'Kabel-Flys von unten','muscle_group'=>'Chest','equipment'=>'Cable'],
            ['name'=>'Kabel-Crossover','muscle_group'=>'Chest','equipment'=>'Cable'],
            ['name'=>'Pec Deck Maschine','muscle_group'=>'Chest','equipment'=>'Machine'],
            ['name'=>'Liegestütz','muscle_group'=>'Chest','equipment'=>'Bodyweight'],
            ['name'=>'Liegestütz eng','muscle_group'=>'Chest','equipment'=>'Bodyweight'],
            ['name'=>'Liegestütz weit','muscle_group'=>'Chest','equipment'=>'Bodyweight'],
            ['name'=>'Diamant-Liegestütz','muscle_group'=>'Chest','equipment'=>'Bodyweight'],
            ['name'=>'Erhöhter Liegestütz','muscle_group'=>'Chest','equipment'=>'Bodyweight'],
            ['name'=>'Brust-Dip','muscle_group'=>'Chest','equipment'=>'Bodyweight'],
            ['name'=>'Bankdrücken LH oder Brustpresse Maschine','muscle_group'=>'Chest','equipment'=>'Machine'],
            ['name'=>'Smith Maschine Bankdrücken','muscle_group'=>'Chest','equipment'=>'Machine'],
            ['name'=>'Smith Maschine Schrägbank','muscle_group'=>'Chest','equipment'=>'Machine'],
            ['name'=>'Langhantel Pullover','muscle_group'=>'Chest','equipment'=>'Barbell'],
            ['name'=>'KH Pullover','muscle_group'=>'Chest','equipment'=>'Dumbbell'],
            ['name'=>'Kabel Pullover','muscle_group'=>'Chest','equipment'=>'Cable'],
            ['name'=>'Brust-Maschine Reverse Grip','muscle_group'=>'Chest','equipment'=>'Machine'],
            ['name'=>'TRX Liegestütz','muscle_group'=>'Chest','equipment'=>'TRX'],
            ['name'=>'Ring Liegestütz','muscle_group'=>'Chest','equipment'=>'Bodyweight'],
            ['name'=>'Planche Liegestütz','muscle_group'=>'Chest','equipment'=>'Bodyweight'],
            ['name'=>'Archer Liegestütz','muscle_group'=>'Chest','equipment'=>'Bodyweight'],
            ['name'=>'Hex Press','muscle_group'=>'Chest','equipment'=>'Dumbbell'],
            ['name'=>'Svend Press','muscle_group'=>'Chest','equipment'=>'Plate'],
            ['name'=>'Landmine Press','muscle_group'=>'Chest','equipment'=>'Barbell'],
            ['name'=>'Kettlebell Bankdrücken','muscle_group'=>'Chest','equipment'=>'Kettlebell'],
            ['name'=>'Brustpresse Maschine Schrägbank','muscle_group'=>'Chest','equipment'=>'Machine'],
            ['name'=>'Einarmiges Kabel-Fly','muscle_group'=>'Chest','equipment'=>'Cable'],
            ['name'=>'Einarmiges KH-Fly','muscle_group'=>'Chest','equipment'=>'Dumbbell'],
            ['name'=>'Gegriffsbank','muscle_group'=>'Chest','equipment'=>'Cable'],
            ['name'=>'Maschinen-Fly','muscle_group'=>'Chest','equipment'=>'Machine'],
            // ── RÜCKEN ──────────────────────────────────────────────────────────
            ['name'=>'Klimmzüge','muscle_group'=>'Back','equipment'=>'Bodyweight'],
            ['name'=>'Klimmzüge mit Zusatzgewicht','muscle_group'=>'Back','equipment'=>'Bodyweight'],
            ['name'=>'Klimmzüge enger Griff','muscle_group'=>'Back','equipment'=>'Bodyweight'],
            ['name'=>'Klimmzüge weiter Griff','muscle_group'=>'Back','equipment'=>'Bodyweight'],
            ['name'=>'Klimmzüge Untergriff','muscle_group'=>'Back','equipment'=>'Bodyweight'],
            ['name'=>'Latziehen weiter Griff','muscle_group'=>'Back','equipment'=>'Cable'],
            ['name'=>'Latziehen neutral / enger Griff','muscle_group'=>'Back','equipment'=>'Cable'],
            ['name'=>'Latziehen Untergriff','muscle_group'=>'Back','equipment'=>'Cable'],
            ['name'=>'Kabelrudern sitzend','muscle_group'=>'Back','equipment'=>'Cable'],
            ['name'=>'Einarmiges Kabelrudern Lat-Fokus','muscle_group'=>'Back','equipment'=>'Cable'],
            ['name'=>'Einarmiges KH-Rudern','muscle_group'=>'Back','equipment'=>'Dumbbell'],
            ['name'=>'LH-Rudern vorgebeugt','muscle_group'=>'Back','equipment'=>'Barbell'],
            ['name'=>'Untergriff LH-Rudern','muscle_group'=>'Back','equipment'=>'Barbell'],
            ['name'=>'T-Bar Row','muscle_group'=>'Back','equipment'=>'Barbell'],
            ['name'=>'T-Bar Row oder Seal Row','muscle_group'=>'Back','equipment'=>'Barbell'],
            ['name'=>'Brustgestütztes Rudern Maschine','muscle_group'=>'Back','equipment'=>'Machine'],
            ['name'=>'Maschinen-Rudern','muscle_group'=>'Back','equipment'=>'Machine'],
            ['name'=>'Seal Row','muscle_group'=>'Back','equipment'=>'Barbell'],
            ['name'=>'Kreuzheben','muscle_group'=>'Back','equipment'=>'Barbell'],
            ['name'=>'Rack Pull','muscle_group'=>'Back','equipment'=>'Barbell'],
            ['name'=>'Hyperextension','muscle_group'=>'Back','equipment'=>'Machine'],
            ['name'=>'Rückenstrecker Maschine','muscle_group'=>'Back','equipment'=>'Machine'],
            ['name'=>'Good Morning','muscle_group'=>'Back','equipment'=>'Barbell'],
            ['name'=>'Straight-Arm Pulldown','muscle_group'=>'Back','equipment'=>'Cable'],
            ['name'=>'Face Pull','muscle_group'=>'Back','equipment'=>'Cable'],
            ['name'=>'Rudermaschine','muscle_group'=>'Back','equipment'=>'Machine'],
            ['name'=>'Shrugs LH','muscle_group'=>'Back','equipment'=>'Barbell'],
            ['name'=>'Shrugs KH','muscle_group'=>'Back','equipment'=>'Dumbbell'],
            ['name'=>'Shrugs Maschine oder Kabel','muscle_group'=>'Back','equipment'=>'Machine'],
            ['name'=>'Invertiertes Rudern','muscle_group'=>'Back','equipment'=>'Bodyweight'],
            ['name'=>'Kreuzheben Sumo','muscle_group'=>'Back','equipment'=>'Barbell'],
            ['name'=>'Trap-Bar Kreuzheben','muscle_group'=>'Back','equipment'=>'Trap Bar'],
            ['name'=>'Pendlay Row','muscle_group'=>'Back','equipment'=>'Barbell'],
            ['name'=>'Yates Row','muscle_group'=>'Back','equipment'=>'Barbell'],
            ['name'=>'Meadows Row','muscle_group'=>'Back','equipment'=>'Barbell'],
            ['name'=>'Kabel-Pullover','muscle_group'=>'Back','equipment'=>'Cable'],
            ['name'=>'Kabelrudern enger Griff','muscle_group'=>'Back','equipment'=>'Cable'],
            ['name'=>'Kabelrudern weiter Griff','muscle_group'=>'Back','equipment'=>'Cable'],
            ['name'=>'KH-Shrugs','muscle_group'=>'Back','equipment'=>'Dumbbell'],
            ['name'=>'Smith Maschine Rudern','muscle_group'=>'Back','equipment'=>'Machine'],
            ['name'=>'Rückenzug Maschine','muscle_group'=>'Back','equipment'=>'Machine'],
            ['name'=>'Lat-Maschine','muscle_group'=>'Back','equipment'=>'Machine'],
            ['name'=>'Assisted Klimmzüge','muscle_group'=>'Back','equipment'=>'Machine'],
            ['name'=>'Kettlebell Rudern','muscle_group'=>'Back','equipment'=>'Kettlebell'],
            ['name'=>'Rack Chins','muscle_group'=>'Back','equipment'=>'Bodyweight'],
            ['name'=>'Jefferson Curl','muscle_group'=>'Back','equipment'=>'Barbell'],
            ['name'=>'Thoracic Extension','muscle_group'=>'Back','equipment'=>'Bodyweight'],
            // ── SCHULTERN ────────────────────────────────────────────────────────
            ['name'=>'Schulterdrücken LH stehend','muscle_group'=>'Shoulders','equipment'=>'Barbell'],
            ['name'=>'Schulterdrücken LH sitzend','muscle_group'=>'Shoulders','equipment'=>'Barbell'],
            ['name'=>'Schulterdrücken KH sitzend','muscle_group'=>'Shoulders','equipment'=>'Dumbbell'],
            ['name'=>'Schulterdrücken KH stehend','muscle_group'=>'Shoulders','equipment'=>'Dumbbell'],
            ['name'=>'Schulterdrücken Maschine','muscle_group'=>'Shoulders','equipment'=>'Machine'],
            ['name'=>'Arnold Press','muscle_group'=>'Shoulders','equipment'=>'Dumbbell'],
            ['name'=>'Push Press','muscle_group'=>'Shoulders','equipment'=>'Barbell'],
            ['name'=>'Seitheben KH','muscle_group'=>'Shoulders','equipment'=>'Dumbbell'],
            ['name'=>'Kabel-Seitheben','muscle_group'=>'Shoulders','equipment'=>'Cable'],
            ['name'=>'Kabel-Seitheben einarmig','muscle_group'=>'Shoulders','equipment'=>'Cable'],
            ['name'=>'Seitheben Maschine','muscle_group'=>'Shoulders','equipment'=>'Machine'],
            ['name'=>'Frontheben KH','muscle_group'=>'Shoulders','equipment'=>'Dumbbell'],
            ['name'=>'Frontheben LH','muscle_group'=>'Shoulders','equipment'=>'Barbell'],
            ['name'=>'Frontheben Kabel','muscle_group'=>'Shoulders','equipment'=>'Cable'],
            ['name'=>'Reverse Pec Deck','muscle_group'=>'Shoulders','equipment'=>'Machine'],
            ['name'=>'Reverse Pec Deck oder Rear Delt Fly','muscle_group'=>'Shoulders','equipment'=>'Machine'],
            ['name'=>'Reverse Pec Deck oder Face Pull leicht','muscle_group'=>'Shoulders','equipment'=>'Machine'],
            ['name'=>'Rear-Delt Fly Kabel oder Maschine','muscle_group'=>'Shoulders','equipment'=>'Cable'],
            ['name'=>'KH-Rear-Delt Fly vorgebeugt','muscle_group'=>'Shoulders','equipment'=>'Dumbbell'],
            ['name'=>'Kabel-Rear-Delt Fly','muscle_group'=>'Shoulders','equipment'=>'Cable'],
            ['name'=>'Upright Row LH','muscle_group'=>'Shoulders','equipment'=>'Barbell'],
            ['name'=>'Upright Row Kabel','muscle_group'=>'Shoulders','equipment'=>'Cable'],
            ['name'=>'Face Pull Seil','muscle_group'=>'Shoulders','equipment'=>'Cable'],
            ['name'=>'Landmine Schulterdrücken','muscle_group'=>'Shoulders','equipment'=>'Barbell'],
            ['name'=>'Kettlebell Schulterdrücken','muscle_group'=>'Shoulders','equipment'=>'Kettlebell'],
            ['name'=>'Band Seitheben','muscle_group'=>'Shoulders','equipment'=>'Band'],
            ['name'=>'Einarmiges Schulterdrücken KH','muscle_group'=>'Shoulders','equipment'=>'Dumbbell'],
            ['name'=>'Lu Raises','muscle_group'=>'Shoulders','equipment'=>'Dumbbell'],
            ['name'=>'Y-T-W Raises','muscle_group'=>'Shoulders','equipment'=>'Dumbbell'],
            ['name'=>'Schulterdrücken Smith Maschine','muscle_group'=>'Shoulders','equipment'=>'Machine'],
            ['name'=>'Bradford Press','muscle_group'=>'Shoulders','equipment'=>'Barbell'],
            ['name'=>'Seitheben Kabel hinter dem Rücken','muscle_group'=>'Shoulders','equipment'=>'Cable'],
            // ── BIZEPS ───────────────────────────────────────────────────────────
            ['name'=>'LH-Curl','muscle_group'=>'Biceps','equipment'=>'Barbell'],
            ['name'=>'EZ-Stange Curl','muscle_group'=>'Biceps','equipment'=>'EZ Bar'],
            ['name'=>'KH-Curl','muscle_group'=>'Biceps','equipment'=>'Dumbbell'],
            ['name'=>'Hammercurl KH','muscle_group'=>'Biceps','equipment'=>'Dumbbell'],
            ['name'=>'Hammer Curl Seil','muscle_group'=>'Biceps','equipment'=>'Cable'],
            ['name'=>'Kabelcurl','muscle_group'=>'Biceps','equipment'=>'Cable'],
            ['name'=>'Kabelcurl einarmig','muscle_group'=>'Biceps','equipment'=>'Cable'],
            ['name'=>'Scottcurl Maschine','muscle_group'=>'Biceps','equipment'=>'Machine'],
            ['name'=>'Scottcurl Maschine oder Kabelcurl','muscle_group'=>'Biceps','equipment'=>'Machine'],
            ['name'=>'Scottcurl LH','muscle_group'=>'Biceps','equipment'=>'Barbell'],
            ['name'=>'Scottcurl EZ','muscle_group'=>'Biceps','equipment'=>'EZ Bar'],
            ['name'=>'Konzentrationscurl','muscle_group'=>'Biceps','equipment'=>'Dumbbell'],
            ['name'=>'Schrägbank KH-Curl','muscle_group'=>'Biceps','equipment'=>'Dumbbell'],
            ['name'=>'Kabelcurl über Kopf','muscle_group'=>'Biceps','equipment'=>'Cable'],
            ['name'=>'Cross Body Hammercurl','muscle_group'=>'Biceps','equipment'=>'Dumbbell'],
            ['name'=>'Reverse Curl LH','muscle_group'=>'Biceps','equipment'=>'Barbell'],
            ['name'=>'Reverse Kabelcurl','muscle_group'=>'Biceps','equipment'=>'Cable'],
            ['name'=>'Zottman Curl','muscle_group'=>'Biceps','equipment'=>'Dumbbell'],
            ['name'=>'Spider Curl','muscle_group'=>'Biceps','equipment'=>'Dumbbell'],
            ['name'=>'Band Curl','muscle_group'=>'Biceps','equipment'=>'Band'],
            ['name'=>'Bizepsmaschine','muscle_group'=>'Biceps','equipment'=>'Machine'],
            ['name'=>'Kabelcurl SZ-Stange','muscle_group'=>'Biceps','equipment'=>'Cable'],
            ['name'=>'Drag Curl','muscle_group'=>'Biceps','equipment'=>'Barbell'],
            ['name'=>'Einarmiger Kabelcurl','muscle_group'=>'Biceps','equipment'=>'Cable'],
            ['name'=>'Trizepsbank Curl','muscle_group'=>'Biceps','equipment'=>'Dumbbell'],
            ['name'=>'21s Curl','muscle_group'=>'Biceps','equipment'=>'Barbell'],
            ['name'=>'Isometrischer Curl','muscle_group'=>'Biceps','equipment'=>'Dumbbell'],
            // ── TRIZEPS ──────────────────────────────────────────────────────────
            ['name'=>'Trizeps Pushdown Seil','muscle_group'=>'Triceps','equipment'=>'Cable'],
            ['name'=>'Trizeps Pushdown Stange','muscle_group'=>'Triceps','equipment'=>'Cable'],
            ['name'=>'Trizeps Pushdown V-Griff','muscle_group'=>'Triceps','equipment'=>'Cable'],
            ['name'=>'Pushdown','muscle_group'=>'Triceps','equipment'=>'Cable'],
            ['name'=>'Overhead Rope Extension','muscle_group'=>'Triceps','equipment'=>'Cable'],
            ['name'=>'Overhead KH Extension','muscle_group'=>'Triceps','equipment'=>'Dumbbell'],
            ['name'=>'Overhead LH Extension','muscle_group'=>'Triceps','equipment'=>'Barbell'],
            ['name'=>'Overhead EZ Extension','muscle_group'=>'Triceps','equipment'=>'EZ Bar'],
            ['name'=>'Skull Crusher LH','muscle_group'=>'Triceps','equipment'=>'Barbell'],
            ['name'=>'Skull Crusher EZ','muscle_group'=>'Triceps','equipment'=>'EZ Bar'],
            ['name'=>'Skull Crusher KH','muscle_group'=>'Triceps','equipment'=>'Dumbbell'],
            ['name'=>'Enges Bankdrücken','muscle_group'=>'Triceps','equipment'=>'Barbell'],
            ['name'=>'Trizepsdip','muscle_group'=>'Triceps','equipment'=>'Bodyweight'],
            ['name'=>'Trizepsdip Maschine','muscle_group'=>'Triceps','equipment'=>'Machine'],
            ['name'=>'KH Kickback','muscle_group'=>'Triceps','equipment'=>'Dumbbell'],
            ['name'=>'Kabel Kickback','muscle_group'=>'Triceps','equipment'=>'Cable'],
            ['name'=>'Einarmiger Trizeps Pushdown','muscle_group'=>'Triceps','equipment'=>'Cable'],
            ['name'=>'JM Press','muscle_group'=>'Triceps','equipment'=>'Barbell'],
            ['name'=>'Trizeps Maschine','muscle_group'=>'Triceps','equipment'=>'Machine'],
            ['name'=>'Band Trizeps Extension','muscle_group'=>'Triceps','equipment'=>'Band'],
            ['name'=>'Trizeps Bankdrücken schmal','muscle_group'=>'Triceps','equipment'=>'Machine'],
            ['name'=>'Reverse Grip Pushdown','muscle_group'=>'Triceps','equipment'=>'Cable'],
            ['name'=>'French Press','muscle_group'=>'Triceps','equipment'=>'Barbell'],
            ['name'=>'French Press EZ','muscle_group'=>'Triceps','equipment'=>'EZ Bar'],
            ['name'=>'Dip Maschine','muscle_group'=>'Triceps','equipment'=>'Machine'],
            ['name'=>'Liegestütz enger Griff','muscle_group'=>'Triceps','equipment'=>'Bodyweight'],
            // ── BEINE ────────────────────────────────────────────────────────────
            ['name'=>'Kniebeuge LH','muscle_group'=>'Legs','equipment'=>'Barbell'],
            ['name'=>'Kniebeuge KH','muscle_group'=>'Legs','equipment'=>'Dumbbell'],
            ['name'=>'High-Bar Kniebeuge','muscle_group'=>'Legs','equipment'=>'Barbell'],
            ['name'=>'Low-Bar Kniebeuge','muscle_group'=>'Legs','equipment'=>'Barbell'],
            ['name'=>'Hack Squat','muscle_group'=>'Legs','equipment'=>'Machine'],
            ['name'=>'Hack Squat oder High-Bar Squat','muscle_group'=>'Legs','equipment'=>'Machine'],
            ['name'=>'Beinpresse','muscle_group'=>'Legs','equipment'=>'Machine'],
            ['name'=>'Beinpresse eng','muscle_group'=>'Legs','equipment'=>'Machine'],
            ['name'=>'Beinpresse weit','muscle_group'=>'Legs','equipment'=>'Machine'],
            ['name'=>'Bulgarische Split Squats','muscle_group'=>'Legs','equipment'=>'Dumbbell'],
            ['name'=>'Bulgarische Split Squats LH','muscle_group'=>'Legs','equipment'=>'Barbell'],
            ['name'=>'Beinstrecker','muscle_group'=>'Legs','equipment'=>'Machine'],
            ['name'=>'Beinbeuger sitzend','muscle_group'=>'Legs','equipment'=>'Machine'],
            ['name'=>'Beinbeuger liegend','muscle_group'=>'Legs','equipment'=>'Machine'],
            ['name'=>'Beinbeuger stehend','muscle_group'=>'Legs','equipment'=>'Machine'],
            ['name'=>'Rumänisches Kreuzheben','muscle_group'=>'Legs','equipment'=>'Barbell'],
            ['name'=>'Rumänisches Kreuzheben KH','muscle_group'=>'Legs','equipment'=>'Dumbbell'],
            ['name'=>'Steifbeiniges Kreuzheben','muscle_group'=>'Legs','equipment'=>'Barbell'],
            ['name'=>'Walking Lunges','muscle_group'=>'Legs','equipment'=>'Dumbbell'],
            ['name'=>'Ausfallschritt','muscle_group'=>'Legs','equipment'=>'Bodyweight'],
            ['name'=>'Ausfallschritt KH','muscle_group'=>'Legs','equipment'=>'Dumbbell'],
            ['name'=>'Ausfallschritt LH','muscle_group'=>'Legs','equipment'=>'Barbell'],
            ['name'=>'Rückwärts Ausfallschritt','muscle_group'=>'Legs','equipment'=>'Dumbbell'],
            ['name'=>'Seitwärts Ausfallschritt','muscle_group'=>'Legs','equipment'=>'Bodyweight'],
            ['name'=>'Pistol Squat','muscle_group'=>'Legs','equipment'=>'Bodyweight'],
            ['name'=>'Box Squat','muscle_group'=>'Legs','equipment'=>'Barbell'],
            ['name'=>'Pause Squat','muscle_group'=>'Legs','equipment'=>'Barbell'],
            ['name'=>'Front Squat','muscle_group'=>'Legs','equipment'=>'Barbell'],
            ['name'=>'Pendulum Squat','muscle_group'=>'Legs','equipment'=>'Machine'],
            ['name'=>'Front Squat Smith Maschine','muscle_group'=>'Legs','equipment'=>'Machine'],
            ['name'=>'Smith Maschine Kniebeuge','muscle_group'=>'Legs','equipment'=>'Machine'],
            ['name'=>'Goblet Squat','muscle_group'=>'Legs','equipment'=>'Dumbbell'],
            ['name'=>'Goblet Squat Kettlebell','muscle_group'=>'Legs','equipment'=>'Kettlebell'],
            ['name'=>'Sumo Kniebeuge','muscle_group'=>'Legs','equipment'=>'Barbell'],
            ['name'=>'Step Up KH','muscle_group'=>'Legs','equipment'=>'Dumbbell'],
            ['name'=>'Step Up LH','muscle_group'=>'Legs','equipment'=>'Barbell'],
            ['name'=>'Box Jump','muscle_group'=>'Legs','equipment'=>'Bodyweight'],
            ['name'=>'Kniebeuge Sprung','muscle_group'=>'Legs','equipment'=>'Bodyweight'],
            ['name'=>'Sissy Squat','muscle_group'=>'Legs','equipment'=>'Bodyweight'],
            ['name'=>'Leg Press einbeinig','muscle_group'=>'Legs','equipment'=>'Machine'],
            ['name'=>'Hackenschmidt','muscle_group'=>'Legs','equipment'=>'Machine'],
            ['name'=>'Kniebeuge mit Pause','muscle_group'=>'Legs','equipment'=>'Barbell'],
            ['name'=>'Trap Bar Kreuzheben','muscle_group'=>'Legs','equipment'=>'Trap Bar'],
            ['name'=>'Nordic Curl','muscle_group'=>'Legs','equipment'=>'Bodyweight'],
            ['name'=>'Kniebeugesprünge','muscle_group'=>'Legs','equipment'=>'Bodyweight'],
            ['name'=>'Beinpresse einbeinig','muscle_group'=>'Legs','equipment'=>'Machine'],
            ['name'=>'Tiefe Kniebeuge','muscle_group'=>'Legs','equipment'=>'Bodyweight'],
            ['name'=>'Jefferson Squat','muscle_group'=>'Legs','equipment'=>'Barbell'],
            ['name'=>'Zercher Squat','muscle_group'=>'Legs','equipment'=>'Barbell'],
            ['name'=>'Kniebeuge Maschine','muscle_group'=>'Legs','equipment'=>'Machine'],
            ['name'=>'Kabel-Ausfallschritt','muscle_group'=>'Legs','equipment'=>'Cable'],
            ['name'=>'Einbeiniges Rumänisches Kreuzheben','muscle_group'=>'Legs','equipment'=>'Dumbbell'],
            ['name'=>'Einbeiniges Rumänisches Kreuzheben LH','muscle_group'=>'Legs','equipment'=>'Barbell'],
            // ── GESÄSS ───────────────────────────────────────────────────────────
            ['name'=>'Hip Thrust LH','muscle_group'=>'Glutes','equipment'=>'Barbell'],
            ['name'=>'Hip Thrust Maschine','muscle_group'=>'Glutes','equipment'=>'Machine'],
            ['name'=>'Hip Thrust oder Glute Bridge Maschine','muscle_group'=>'Glutes','equipment'=>'Machine'],
            ['name'=>'Hip Thrust KH','muscle_group'=>'Glutes','equipment'=>'Dumbbell'],
            ['name'=>'Glute Bridge','muscle_group'=>'Glutes','equipment'=>'Bodyweight'],
            ['name'=>'Glute Bridge LH','muscle_group'=>'Glutes','equipment'=>'Barbell'],
            ['name'=>'Glute Bridge einbeinig','muscle_group'=>'Glutes','equipment'=>'Bodyweight'],
            ['name'=>'Kabel-Kickback','muscle_group'=>'Glutes','equipment'=>'Cable'],
            ['name'=>'Maschinen-Kickback','muscle_group'=>'Glutes','equipment'=>'Machine'],
            ['name'=>'Hüftabduktion Maschine','muscle_group'=>'Glutes','equipment'=>'Machine'],
            ['name'=>'Hüftabduktion Kabel','muscle_group'=>'Glutes','equipment'=>'Cable'],
            ['name'=>'Hüftadduktion Maschine','muscle_group'=>'Glutes','equipment'=>'Machine'],
            ['name'=>'Donkey Kick','muscle_group'=>'Glutes','equipment'=>'Bodyweight'],
            ['name'=>'Donkey Kick Kabel','muscle_group'=>'Glutes','equipment'=>'Cable'],
            ['name'=>'Squat Pulse','muscle_group'=>'Glutes','equipment'=>'Bodyweight'],
            ['name'=>'Sumo Deadlift','muscle_group'=>'Glutes','equipment'=>'Barbell'],
            ['name'=>'American Hip Thrust','muscle_group'=>'Glutes','equipment'=>'Barbell'],
            ['name'=>'Kniebeuge tief Fokus Gesäss','muscle_group'=>'Glutes','equipment'=>'Barbell'],
            ['name'=>'Froschpresse','muscle_group'=>'Glutes','equipment'=>'Bodyweight'],
            ['name'=>'45-Grad Rückenverlängerung Gesäss','muscle_group'=>'Glutes','equipment'=>'Machine'],
            ['name'=>'Kabel-Hip-Flexion','muscle_group'=>'Glutes','equipment'=>'Cable'],
            ['name'=>'Fire Hydrant','muscle_group'=>'Glutes','equipment'=>'Bodyweight'],
            ['name'=>'Hip Thrust Band','muscle_group'=>'Glutes','equipment'=>'Band'],
            // ── WADEN ────────────────────────────────────────────────────────────
            ['name'=>'Wadenheben stehend','muscle_group'=>'Calves','equipment'=>'Machine'],
            ['name'=>'Wadenheben sitzend','muscle_group'=>'Calves','equipment'=>'Machine'],
            ['name'=>'Wadenheben einbeinig','muscle_group'=>'Calves','equipment'=>'Bodyweight'],
            ['name'=>'Wadenheben LH','muscle_group'=>'Calves','equipment'=>'Barbell'],
            ['name'=>'Wadenheben KH','muscle_group'=>'Calves','equipment'=>'Dumbbell'],
            ['name'=>'Wadenheben Beinpresse','muscle_group'=>'Calves','equipment'=>'Machine'],
            ['name'=>'Wadenheben Smith Maschine','muscle_group'=>'Calves','equipment'=>'Machine'],
            ['name'=>'Wadenheben Stufe','muscle_group'=>'Calves','equipment'=>'Bodyweight'],
            ['name'=>'Wadenheben Kettlebell','muscle_group'=>'Calves','equipment'=>'Kettlebell'],
            ['name'=>'Tibiaheben','muscle_group'=>'Calves','equipment'=>'Bodyweight'],
            ['name'=>'Donkey Calf Raise','muscle_group'=>'Calves','equipment'=>'Machine'],
            ['name'=>'Wadenmaschine stehend','muscle_group'=>'Calves','equipment'=>'Machine'],
            ['name'=>'Wadendehnung Kabel','muscle_group'=>'Calves','equipment'=>'Cable'],
            ['name'=>'Band Wadenheben','muscle_group'=>'Calves','equipment'=>'Band'],
            // ── CORE / BAUCH ─────────────────────────────────────────────────────
            ['name'=>'Plank','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            ['name'=>'Plank seitlich','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            ['name'=>'Plank mit Armheben','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            ['name'=>'Crunch','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            ['name'=>'Fahrrad-Crunch','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            ['name'=>'Russische Drehung','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            ['name'=>'Russische Drehung mit Gewicht','muscle_group'=>'Core','equipment'=>'Plate'],
            ['name'=>'Beinhebenliegen','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            ['name'=>'Hängendes Beinheben','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            ['name'=>'Hängendes Knieheben','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            ['name'=>'Ab Rollout','muscle_group'=>'Core','equipment'=>'Ab Wheel'],
            ['name'=>'Kabel-Crunch','muscle_group'=>'Core','equipment'=>'Cable'],
            ['name'=>'Bergsteiger','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            ['name'=>'Dead Bug','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            ['name'=>'Dragon Flag','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            ['name'=>'V-Up','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            ['name'=>'Hollow Body Hold','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            ['name'=>'Pallof Press','muscle_group'=>'Core','equipment'=>'Cable'],
            ['name'=>'Windmühle KH','muscle_group'=>'Core','equipment'=>'Kettlebell'],
            ['name'=>'Crunch Maschine','muscle_group'=>'Core','equipment'=>'Machine'],
            ['name'=>'Sit-Up','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            ['name'=>'Scheibenwischer','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            ['name'=>'Bauchpresse Maschine','muscle_group'=>'Core','equipment'=>'Machine'],
            ['name'=>'Holzhacker Kabel','muscle_group'=>'Core','equipment'=>'Cable'],
            ['name'=>'Landmine Rotation','muscle_group'=>'Core','equipment'=>'Barbell'],
            ['name'=>'Ab Wheel Rollout kniend','muscle_group'=>'Core','equipment'=>'Ab Wheel'],
            ['name'=>'TRX Crunch','muscle_group'=>'Core','equipment'=>'TRX'],
            ['name'=>'Stab-Crunch','muscle_group'=>'Core','equipment'=>'Barbell'],
            ['name'=>'Beinheben Schräg','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            ['name'=>'Hüftkreisen','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            ['name'=>'Copenhagen Plank','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            ['name'=>'Bodysaw','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            ['name'=>'Reverse Crunch','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            ['name'=>'Oblique Crunch','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            ['name'=>'Kabelcrunch seitlich','muscle_group'=>'Core','equipment'=>'Cable'],
            ['name'=>'Bauchvakuum','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            ['name'=>'L-Sit','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            ['name'=>'Toe Touch Crunch','muscle_group'=>'Core','equipment'=>'Bodyweight'],
            // ── CARDIO ───────────────────────────────────────────────────────────
            ['name'=>'Laufen','muscle_group'=>'Cardio','equipment'=>'None'],
            ['name'=>'Sprinten','muscle_group'=>'Cardio','equipment'=>'None'],
            ['name'=>'Radfahren','muscle_group'=>'Cardio','equipment'=>'Bike'],
            ['name'=>'Rudermaschine Cardio','muscle_group'=>'Cardio','equipment'=>'Machine'],
            ['name'=>'Springseil','muscle_group'=>'Cardio','equipment'=>'Jump Rope'],
            ['name'=>'Battle Ropes','muscle_group'=>'Cardio','equipment'=>'Battle Ropes'],
            ['name'=>'Burpee','muscle_group'=>'Cardio','equipment'=>'Bodyweight'],
            ['name'=>'Jumping Jacks','muscle_group'=>'Cardio','equipment'=>'Bodyweight'],
            ['name'=>'Stepper','muscle_group'=>'Cardio','equipment'=>'Machine'],
            ['name'=>'Ellipsentrainer','muscle_group'=>'Cardio','equipment'=>'Machine'],
            ['name'=>'Schwimmen','muscle_group'=>'Cardio','equipment'=>'None'],
            ['name'=>'HIIT Laufband','muscle_group'=>'Cardio','equipment'=>'Machine'],
            ['name'=>'Seilklettern','muscle_group'=>'Cardio','equipment'=>'Bodyweight'],
            ['name'=>'Box Jump','muscle_group'=>'Cardio','equipment'=>'Bodyweight'],
            ['name'=>'Ski Erg','muscle_group'=>'Cardio','equipment'=>'Machine'],
            ['name'=>'Assault Bike','muscle_group'=>'Cardio','equipment'=>'Bike'],
            ['name'=>'Sled Push','muscle_group'=>'Cardio','equipment'=>'Sled'],
            ['name'=>'Sled Pull','muscle_group'=>'Cardio','equipment'=>'Sled'],
            ['name'=>'Treppenlaufen','muscle_group'=>'Cardio','equipment'=>'None'],
            ['name'=>'Shadowboxen','muscle_group'=>'Cardio','equipment'=>'None'],
            // ── GANZKÖRPER ───────────────────────────────────────────────────────
            ['name'=>'Kettlebell Swing','muscle_group'=>'Full Body','equipment'=>'Kettlebell'],
            ['name'=>'Kettlebell Clean','muscle_group'=>'Full Body','equipment'=>'Kettlebell'],
            ['name'=>'Kettlebell Snatch','muscle_group'=>'Full Body','equipment'=>'Kettlebell'],
            ['name'=>'Kettlebell TGU','muscle_group'=>'Full Body','equipment'=>'Kettlebell'],
            ['name'=>'Farmers Walk','muscle_group'=>'Full Body','equipment'=>'Dumbbell'],
            ['name'=>'Farmers Walk KH','muscle_group'=>'Full Body','equipment'=>'Dumbbell'],
            ['name'=>'Farmers Walk LH','muscle_group'=>'Full Body','equipment'=>'Barbell'],
            ['name'=>'Thruster LH','muscle_group'=>'Full Body','equipment'=>'Barbell'],
            ['name'=>'Thruster KH','muscle_group'=>'Full Body','equipment'=>'Dumbbell'],
            ['name'=>'Clean and Press','muscle_group'=>'Full Body','equipment'=>'Barbell'],
            ['name'=>'Hang Clean','muscle_group'=>'Full Body','equipment'=>'Barbell'],
            ['name'=>'Power Clean','muscle_group'=>'Full Body','equipment'=>'Barbell'],
            ['name'=>'Snatch LH','muscle_group'=>'Full Body','equipment'=>'Barbell'],
            ['name'=>'Turkish Get-Up','muscle_group'=>'Full Body','equipment'=>'Kettlebell'],
            ['name'=>'Sandbag Carry','muscle_group'=>'Full Body','equipment'=>'Sandbag'],
            ['name'=>'Medizinball Slam','muscle_group'=>'Full Body','equipment'=>'Medicine Ball'],
            ['name'=>'Medizinball Wurf','muscle_group'=>'Full Body','equipment'=>'Medicine Ball'],
            ['name'=>'Renegade Row','muscle_group'=>'Full Body','equipment'=>'Dumbbell'],
            ['name'=>'Man Maker','muscle_group'=>'Full Body','equipment'=>'Dumbbell'],
            ['name'=>'Devil Press','muscle_group'=>'Full Body','equipment'=>'Dumbbell'],
            ['name'=>'Bear Complex','muscle_group'=>'Full Body','equipment'=>'Barbell'],
            ['name'=>'Kofferträgerlauf','muscle_group'=>'Full Body','equipment'=>'Dumbbell'],
            ['name'=>'Yoke Walk','muscle_group'=>'Full Body','equipment'=>'Barbell'],
            ['name'=>'Kettlebell Bottoms Up Press','muscle_group'=>'Full Body','equipment'=>'Kettlebell'],
            ['name'=>'Tire Flip','muscle_group'=>'Full Body','equipment'=>'Tire'],
            // ── UNTERARME ────────────────────────────────────────────────────────
            ['name'=>'Handgelenk-Curl LH','muscle_group'=>'Full Body','equipment'=>'Barbell'],
            ['name'=>'Handgelenk-Curl KH','muscle_group'=>'Full Body','equipment'=>'Dumbbell'],
            ['name'=>'Reverse Handgelenk-Curl','muscle_group'=>'Full Body','equipment'=>'Barbell'],
            ['name'=>'Unterarmrollen','muscle_group'=>'Full Body','equipment'=>'Plate'],
            ['name'=>'Griffkraft Kreisel','muscle_group'=>'Full Body','equipment'=>'None'],
            ['name'=>'Towel Pull-Up','muscle_group'=>'Back','equipment'=>'Bodyweight'],
            ['name'=>'Plate Pinch','muscle_group'=>'Full Body','equipment'=>'Plate'],
            ['name'=>'Fat Gripz Curl','muscle_group'=>'Biceps','equipment'=>'Barbell'],
            ['name'=>'Kabelrudern Unterarm-Fokus','muscle_group'=>'Back','equipment'=>'Cable'],
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
    Route::get('/admin/users/{id}/training-plans', function (int $id) {
        abort_if(!auth()->user()->is_admin, 403);
        $plans = \Illuminate\Support\Facades\DB::table('training_plans')
            ->where('user_id', $id)
            ->orderBy('created_at','desc')
            ->get();
        foreach ($plans as $plan) {
            $plan->exercises = \Illuminate\Support\Facades\DB::table('training_plan_exercises')
                ->where('training_plan_id', $plan->id)
                ->orderBy('order_index')
                ->get();
        }
        return response()->json($plans);
    });
    Route::post('/admin/users/{id}/training-plans', function (\Illuminate\Http\Request $req, int $id) {
        abort_if(!auth()->user()->is_admin, 403);
        abort_if(!\Illuminate\Support\Facades\DB::table('users')->find($id), 404);
        $v = $req->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'template_type'  => 'nullable|string|max:50',
            'duration_weeks' => 'nullable|integer|min:1',
        ]);
        $planId = \Illuminate\Support\Facades\DB::table('training_plans')->insertGetId(array_merge($v, [
            'user_id'    => $id,
            'status'     => 'Active',
            'created_at' => now(),
            'updated_at' => now(),
        ]));
        $plan = \Illuminate\Support\Facades\DB::table('training_plans')->find($planId);
        $plan->exercises = [];
        return response()->json($plan, 201);
    });
    Route::post('/admin/training-plans/{planId}/exercises', function (\Illuminate\Http\Request $req, int $planId) {
        abort_if(!auth()->user()->is_admin, 403);
        $plan = \Illuminate\Support\Facades\DB::table('training_plans')->find($planId);
        abort_if(!$plan, 404);
        $v = $req->validate([
            'exercise_name'  => 'required|string|max:255',
            'muscle_group'   => 'nullable|string',
            'target_sets'    => 'nullable|integer|min:1',
            'target_reps'    => 'nullable|string|max:20',
            'target_weight'  => 'nullable|numeric',
            'weight_unit'    => 'nullable|string|max:10',
            'target_rir'     => 'nullable|integer|min:0|max:5',
            'rest_seconds'   => 'nullable|integer',
            'notes'          => 'nullable|string',
        ]);
        $count = \Illuminate\Support\Facades\DB::table('training_plan_exercises')->where('training_plan_id',$planId)->count();
        $id = \Illuminate\Support\Facades\DB::table('training_plan_exercises')->insertGetId(array_merge($v, [
            'training_plan_id' => $planId,
            'order_index'      => $count + 1,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]));
        return response()->json(\Illuminate\Support\Facades\DB::table('training_plan_exercises')->find($id), 201);
    });
    Route::delete('/admin/training-plans/{planId}/exercises/{exId}', function (int $planId, int $exId) {
        abort_if(!auth()->user()->is_admin, 403);
        \Illuminate\Support\Facades\DB::table('training_plan_exercises')
            ->where('training_plan_id', $planId)->where('id', $exId)->delete();
        return response()->json(['message' => 'Deleted']);
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
    Route::put('/admin/exercises/{id}', function (\Illuminate\Http\Request $req, int $id) {
        abort_if(!auth()->user()->is_admin, 403);
        abort_if(!\Illuminate\Support\Facades\DB::table('custom_exercises')->find($id), 404);
        $v = $req->validate([
            'name'         => 'required|string|max:255',
            'muscle_group' => 'required|string',
            'equipment'    => 'nullable|string',
            'instructions' => 'nullable|string',
        ]);
        \Illuminate\Support\Facades\DB::table('custom_exercises')->where('id',$id)->update(array_merge($v,['updated_at'=>now()]));
        return response()->json(\Illuminate\Support\Facades\DB::table('custom_exercises')->find($id));
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
