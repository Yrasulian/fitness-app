<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExerciseSeeder extends Seeder
{
    public function run(): void
    {
        $exercises = [
            // ── CHEST ──────────────────────────────────────────────
            ['name' => 'Barbell Bench Press', 'muscle_group' => 'Chest', 'secondary_muscles' => 'Triceps, Front Delts', 'equipment' => 'Barbell', 'type' => 'Strength'],
            ['name' => 'Incline Barbell Bench Press', 'muscle_group' => 'Chest', 'secondary_muscles' => 'Triceps, Front Delts', 'equipment' => 'Barbell', 'type' => 'Strength'],
            ['name' => 'Decline Barbell Bench Press', 'muscle_group' => 'Chest', 'secondary_muscles' => 'Triceps', 'equipment' => 'Barbell', 'type' => 'Strength'],
            ['name' => 'Dumbbell Bench Press', 'muscle_group' => 'Chest', 'secondary_muscles' => 'Triceps, Front Delts', 'equipment' => 'Dumbbell', 'type' => 'Strength'],
            ['name' => 'Incline Dumbbell Press', 'muscle_group' => 'Chest', 'secondary_muscles' => 'Triceps, Front Delts', 'equipment' => 'Dumbbell', 'type' => 'Strength'],
            ['name' => 'Decline Dumbbell Press', 'muscle_group' => 'Chest', 'secondary_muscles' => 'Triceps', 'equipment' => 'Dumbbell', 'type' => 'Strength'],
            ['name' => 'Dumbbell Flyes', 'muscle_group' => 'Chest', 'secondary_muscles' => 'Front Delts', 'equipment' => 'Dumbbell', 'type' => 'Strength'],
            ['name' => 'Incline Dumbbell Flyes', 'muscle_group' => 'Chest', 'secondary_muscles' => 'Front Delts', 'equipment' => 'Dumbbell', 'type' => 'Strength'],
            ['name' => 'Cable Crossover', 'muscle_group' => 'Chest', 'secondary_muscles' => 'Front Delts', 'equipment' => 'Cable', 'type' => 'Strength'],
            ['name' => 'Low Cable Fly', 'muscle_group' => 'Chest', 'secondary_muscles' => null, 'equipment' => 'Cable', 'type' => 'Strength'],
            ['name' => 'High Cable Fly', 'muscle_group' => 'Chest', 'secondary_muscles' => null, 'equipment' => 'Cable', 'type' => 'Strength'],
            ['name' => 'Push-Up', 'muscle_group' => 'Chest', 'secondary_muscles' => 'Triceps, Front Delts', 'equipment' => 'Bodyweight', 'type' => 'Strength'],
            ['name' => 'Wide Push-Up', 'muscle_group' => 'Chest', 'secondary_muscles' => 'Triceps', 'equipment' => 'Bodyweight', 'type' => 'Strength'],
            ['name' => 'Diamond Push-Up', 'muscle_group' => 'Chest', 'secondary_muscles' => 'Triceps', 'equipment' => 'Bodyweight', 'type' => 'Strength'],
            ['name' => 'Chest Dips', 'muscle_group' => 'Chest', 'secondary_muscles' => 'Triceps, Front Delts', 'equipment' => 'Bodyweight', 'type' => 'Strength'],
            ['name' => 'Machine Chest Press', 'muscle_group' => 'Chest', 'secondary_muscles' => 'Triceps', 'equipment' => 'Machine', 'type' => 'Strength'],
            ['name' => 'Pec Deck / Butterfly', 'muscle_group' => 'Chest', 'secondary_muscles' => null, 'equipment' => 'Machine', 'type' => 'Strength'],
            ['name' => 'Smith Machine Bench Press', 'muscle_group' => 'Chest', 'secondary_muscles' => 'Triceps', 'equipment' => 'Machine', 'type' => 'Strength'],

            // ── BACK ───────────────────────────────────────────────
            ['name' => 'Pull-Up', 'muscle_group' => 'Back', 'secondary_muscles' => 'Biceps', 'equipment' => 'Bodyweight', 'type' => 'Strength'],
            ['name' => 'Chin-Up', 'muscle_group' => 'Back', 'secondary_muscles' => 'Biceps', 'equipment' => 'Bodyweight', 'type' => 'Strength'],
            ['name' => 'Wide Grip Pull-Up', 'muscle_group' => 'Back', 'secondary_muscles' => 'Biceps', 'equipment' => 'Bodyweight', 'type' => 'Strength'],
            ['name' => 'Neutral Grip Pull-Up', 'muscle_group' => 'Back', 'secondary_muscles' => 'Biceps', 'equipment' => 'Bodyweight', 'type' => 'Strength'],
            ['name' => 'Lat Pulldown', 'muscle_group' => 'Back', 'secondary_muscles' => 'Biceps', 'equipment' => 'Cable', 'type' => 'Strength'],
            ['name' => 'Wide Grip Lat Pulldown', 'muscle_group' => 'Back', 'secondary_muscles' => 'Biceps', 'equipment' => 'Cable', 'type' => 'Strength'],
            ['name' => 'Close Grip Lat Pulldown', 'muscle_group' => 'Back', 'secondary_muscles' => 'Biceps', 'equipment' => 'Cable', 'type' => 'Strength'],
            ['name' => 'Seated Cable Row', 'muscle_group' => 'Back', 'secondary_muscles' => 'Biceps, Rear Delts', 'equipment' => 'Cable', 'type' => 'Strength'],
            ['name' => 'Bent Over Barbell Row', 'muscle_group' => 'Back', 'secondary_muscles' => 'Biceps, Rear Delts', 'equipment' => 'Barbell', 'type' => 'Strength'],
            ['name' => 'Bent Over Dumbbell Row', 'muscle_group' => 'Back', 'secondary_muscles' => 'Biceps', 'equipment' => 'Dumbbell', 'type' => 'Strength'],
            ['name' => 'One Arm Dumbbell Row', 'muscle_group' => 'Back', 'secondary_muscles' => 'Biceps', 'equipment' => 'Dumbbell', 'type' => 'Strength'],
            ['name' => 'T-Bar Row', 'muscle_group' => 'Back', 'secondary_muscles' => 'Biceps, Rear Delts', 'equipment' => 'Barbell', 'type' => 'Strength'],
            ['name' => 'Deadlift', 'muscle_group' => 'Back', 'secondary_muscles' => 'Glutes, Hamstrings, Traps', 'equipment' => 'Barbell', 'type' => 'Strength'],
            ['name' => 'Romanian Deadlift', 'muscle_group' => 'Back', 'secondary_muscles' => 'Hamstrings, Glutes', 'equipment' => 'Barbell', 'type' => 'Strength'],
            ['name' => 'Rack Pull', 'muscle_group' => 'Back', 'secondary_muscles' => 'Traps, Glutes', 'equipment' => 'Barbell', 'type' => 'Strength'],
            ['name' => 'Good Morning', 'muscle_group' => 'Back', 'secondary_muscles' => 'Hamstrings', 'equipment' => 'Barbell', 'type' => 'Strength'],
            ['name' => 'Hyperextension', 'muscle_group' => 'Back', 'secondary_muscles' => 'Glutes, Hamstrings', 'equipment' => 'Machine', 'type' => 'Strength'],
            ['name' => 'Face Pull', 'muscle_group' => 'Back', 'secondary_muscles' => 'Rear Delts, Rotator Cuff', 'equipment' => 'Cable', 'type' => 'Strength'],
            ['name' => 'Straight Arm Pulldown', 'muscle_group' => 'Back', 'secondary_muscles' => null, 'equipment' => 'Cable', 'type' => 'Strength'],
            ['name' => 'Inverted Row', 'muscle_group' => 'Back', 'secondary_muscles' => 'Biceps', 'equipment' => 'Bodyweight', 'type' => 'Strength'],
            ['name' => 'Machine Row', 'muscle_group' => 'Back', 'secondary_muscles' => 'Biceps', 'equipment' => 'Machine', 'type' => 'Strength'],
            ['name' => 'Dumbbell Pullover', 'muscle_group' => 'Back', 'secondary_muscles' => 'Chest, Triceps', 'equipment' => 'Dumbbell', 'type' => 'Strength'],
            ['name' => 'Barbell Shrug', 'muscle_group' => 'Back', 'secondary_muscles' => null, 'equipment' => 'Barbell', 'type' => 'Strength'],
            ['name' => 'Dumbbell Shrug', 'muscle_group' => 'Back', 'secondary_muscles' => null, 'equipment' => 'Dumbbell', 'type' => 'Strength'],

            // ── SHOULDERS ──────────────────────────────────────────
            ['name' => 'Overhead Press (OHP)', 'muscle_group' => 'Shoulders', 'secondary_muscles' => 'Triceps, Upper Chest', 'equipment' => 'Barbell', 'type' => 'Strength'],
            ['name' => 'Seated Dumbbell Shoulder Press', 'muscle_group' => 'Shoulders', 'secondary_muscles' => 'Triceps', 'equipment' => 'Dumbbell', 'type' => 'Strength'],
            ['name' => 'Arnold Press', 'muscle_group' => 'Shoulders', 'secondary_muscles' => 'Triceps', 'equipment' => 'Dumbbell', 'type' => 'Strength'],
            ['name' => 'Military Press', 'muscle_group' => 'Shoulders', 'secondary_muscles' => 'Triceps', 'equipment' => 'Barbell', 'type' => 'Strength'],
            ['name' => 'Lateral Raise', 'muscle_group' => 'Shoulders', 'secondary_muscles' => null, 'equipment' => 'Dumbbell', 'type' => 'Strength'],
            ['name' => 'Cable Lateral Raise', 'muscle_group' => 'Shoulders', 'secondary_muscles' => null, 'equipment' => 'Cable', 'type' => 'Strength'],
            ['name' => 'Front Raise', 'muscle_group' => 'Shoulders', 'secondary_muscles' => null, 'equipment' => 'Dumbbell', 'type' => 'Strength'],
            ['name' => 'Rear Delt Fly', 'muscle_group' => 'Shoulders', 'secondary_muscles' => 'Traps', 'equipment' => 'Dumbbell', 'type' => 'Strength'],
            ['name' => 'Reverse Pec Deck', 'muscle_group' => 'Shoulders', 'secondary_muscles' => 'Traps', 'equipment' => 'Machine', 'type' => 'Strength'],
            ['name' => 'Upright Row', 'muscle_group' => 'Shoulders', 'secondary_muscles' => 'Traps, Biceps', 'equipment' => 'Barbell', 'type' => 'Strength'],
            ['name' => 'Machine Shoulder Press', 'muscle_group' => 'Shoulders', 'secondary_muscles' => 'Triceps', 'equipment' => 'Machine', 'type' => 'Strength'],
            ['name' => 'Plate Front Raise', 'muscle_group' => 'Shoulders', 'secondary_muscles' => null, 'equipment' => 'Other', 'type' => 'Strength'],
            ['name' => 'Cable Rear Delt Fly', 'muscle_group' => 'Shoulders', 'secondary_muscles' => null, 'equipment' => 'Cable', 'type' => 'Strength'],

            // ── BICEPS ─────────────────────────────────────────────
            ['name' => 'Barbell Curl', 'muscle_group' => 'Biceps', 'secondary_muscles' => 'Forearms', 'equipment' => 'Barbell', 'type' => 'Strength'],
            ['name' => 'EZ Bar Curl', 'muscle_group' => 'Biceps', 'secondary_muscles' => 'Forearms', 'equipment' => 'Barbell', 'type' => 'Strength'],
            ['name' => 'Dumbbell Curl', 'muscle_group' => 'Biceps', 'secondary_muscles' => 'Forearms', 'equipment' => 'Dumbbell', 'type' => 'Strength'],
            ['name' => 'Hammer Curl', 'muscle_group' => 'Biceps', 'secondary_muscles' => 'Forearms, Brachialis', 'equipment' => 'Dumbbell', 'type' => 'Strength'],
            ['name' => 'Preacher Curl', 'muscle_group' => 'Biceps', 'secondary_muscles' => null, 'equipment' => 'Barbell', 'type' => 'Strength'],
            ['name' => 'Concentration Curl', 'muscle_group' => 'Biceps', 'secondary_muscles' => null, 'equipment' => 'Dumbbell', 'type' => 'Strength'],
            ['name' => 'Cable Curl', 'muscle_group' => 'Biceps', 'secondary_muscles' => null, 'equipment' => 'Cable', 'type' => 'Strength'],
            ['name' => 'Incline Dumbbell Curl', 'muscle_group' => 'Biceps', 'secondary_muscles' => null, 'equipment' => 'Dumbbell', 'type' => 'Strength'],
            ['name' => 'Spider Curl', 'muscle_group' => 'Biceps', 'secondary_muscles' => null, 'equipment' => 'Dumbbell', 'type' => 'Strength'],
            ['name' => 'Reverse Curl', 'muscle_group' => 'Biceps', 'secondary_muscles' => 'Forearms, Brachialis', 'equipment' => 'Barbell', 'type' => 'Strength'],
            ['name' => 'Zottman Curl', 'muscle_group' => 'Biceps', 'secondary_muscles' => 'Forearms', 'equipment' => 'Dumbbell', 'type' => 'Strength'],
            ['name' => 'Machine Curl', 'muscle_group' => 'Biceps', 'secondary_muscles' => null, 'equipment' => 'Machine', 'type' => 'Strength'],
            ['name' => 'Cross Body Hammer Curl', 'muscle_group' => 'Biceps', 'secondary_muscles' => 'Brachialis', 'equipment' => 'Dumbbell', 'type' => 'Strength'],

            // ── TRICEPS ────────────────────────────────────────────
            ['name' => 'Tricep Pushdown (Rope)', 'muscle_group' => 'Triceps', 'secondary_muscles' => null, 'equipment' => 'Cable', 'type' => 'Strength'],
            ['name' => 'Tricep Pushdown (Bar)', 'muscle_group' => 'Triceps', 'secondary_muscles' => null, 'equipment' => 'Cable', 'type' => 'Strength'],
            ['name' => 'Skull Crusher (EZ Bar)', 'muscle_group' => 'Triceps', 'secondary_muscles' => null, 'equipment' => 'Barbell', 'type' => 'Strength'],
            ['name' => 'Skull Crusher (Dumbbell)', 'muscle_group' => 'Triceps', 'secondary_muscles' => null, 'equipment' => 'Dumbbell', 'type' => 'Strength'],
            ['name' => 'Close Grip Bench Press', 'muscle_group' => 'Triceps', 'secondary_muscles' => 'Chest, Front Delts', 'equipment' => 'Barbell', 'type' => 'Strength'],
            ['name' => 'Overhead Tricep Extension (Cable)', 'muscle_group' => 'Triceps', 'secondary_muscles' => null, 'equipment' => 'Cable', 'type' => 'Strength'],
            ['name' => 'Overhead Tricep Extension (Dumbbell)', 'muscle_group' => 'Triceps', 'secondary_muscles' => null, 'equipment' => 'Dumbbell', 'type' => 'Strength'],
            ['name' => 'Tricep Dips', 'muscle_group' => 'Triceps', 'secondary_muscles' => 'Chest', 'equipment' => 'Bodyweight', 'type' => 'Strength'],
            ['name' => 'Tricep Kickback', 'muscle_group' => 'Triceps', 'secondary_muscles' => null, 'equipment' => 'Dumbbell', 'type' => 'Strength'],
            ['name' => 'Machine Tricep Extension', 'muscle_group' => 'Triceps', 'secondary_muscles' => null, 'equipment' => 'Machine', 'type' => 'Strength'],

            // ── QUADS ──────────────────────────────────────────────
            ['name' => 'Barbell Back Squat', 'muscle_group' => 'Quads', 'secondary_muscles' => 'Glutes, Hamstrings', 'equipment' => 'Barbell', 'type' => 'Strength'],
            ['name' => 'Front Squat', 'muscle_group' => 'Quads', 'secondary_muscles' => 'Glutes, Hamstrings', 'equipment' => 'Barbell', 'type' => 'Strength'],
            ['name' => 'Goblet Squat', 'muscle_group' => 'Quads', 'secondary_muscles' => 'Glutes', 'equipment' => 'Dumbbell', 'type' => 'Strength'],
            ['name' => 'Hack Squat', 'muscle_group' => 'Quads', 'secondary_muscles' => 'Glutes', 'equipment' => 'Machine', 'type' => 'Strength'],
            ['name' => 'Leg Press', 'muscle_group' => 'Quads', 'secondary_muscles' => 'Glutes, Hamstrings', 'equipment' => 'Machine', 'type' => 'Strength'],
            ['name' => 'Bulgarian Split Squat', 'muscle_group' => 'Quads', 'secondary_muscles' => 'Glutes, Hamstrings', 'equipment' => 'Dumbbell', 'type' => 'Strength'],
            ['name' => 'Lunge', 'muscle_group' => 'Quads', 'secondary_muscles' => 'Glutes, Hamstrings', 'equipment' => 'Dumbbell', 'type' => 'Strength'],
            ['name' => 'Walking Lunge', 'muscle_group' => 'Quads', 'secondary_muscles' => 'Glutes', 'equipment' => 'Dumbbell', 'type' => 'Strength'],
            ['name' => 'Leg Extension', 'muscle_group' => 'Quads', 'secondary_muscles' => null, 'equipment' => 'Machine', 'type' => 'Strength'],
            ['name' => 'Step Up', 'muscle_group' => 'Quads', 'secondary_muscles' => 'Glutes', 'equipment' => 'Dumbbell', 'type' => 'Strength'],
            ['name' => 'Sissy Squat', 'muscle_group' => 'Quads', 'secondary_muscles' => null, 'equipment' => 'Bodyweight', 'type' => 'Strength'],
            ['name' => 'Smith Machine Squat', 'muscle_group' => 'Quads', 'secondary_muscles' => 'Glutes', 'equipment' => 'Machine', 'type' => 'Strength'],
            ['name' => 'Box Squat', 'muscle_group' => 'Quads', 'secondary_muscles' => 'Glutes, Hamstrings', 'equipment' => 'Barbell', 'type' => 'Strength'],
            ['name' => 'Sumo Squat', 'muscle_group' => 'Quads', 'secondary_muscles' => 'Glutes, Adductors', 'equipment' => 'Dumbbell', 'type' => 'Strength'],

            // ── HAMSTRINGS ─────────────────────────────────────────
            ['name' => 'Romanian Deadlift (RDL)', 'muscle_group' => 'Hamstrings', 'secondary_muscles' => 'Glutes, Lower Back', 'equipment' => 'Barbell', 'type' => 'Strength'],
            ['name' => 'Stiff Leg Deadlift', 'muscle_group' => 'Hamstrings', 'secondary_muscles' => 'Glutes', 'equipment' => 'Barbell', 'type' => 'Strength'],
            ['name' => 'Lying Leg Curl', 'muscle_group' => 'Hamstrings', 'secondary_muscles' => null, 'equipment' => 'Machine', 'type' => 'Strength'],
            ['name' => 'Seated Leg Curl', 'muscle_group' => 'Hamstrings', 'secondary_muscles' => null, 'equipment' => 'Machine', 'type' => 'Strength'],
            ['name' => 'Nordic Hamstring Curl', 'muscle_group' => 'Hamstrings', 'secondary_muscles' => null, 'equipment' => 'Bodyweight', 'type' => 'Strength'],
            ['name' => 'Sumo Deadlift', 'muscle_group' => 'Hamstrings', 'secondary_muscles' => 'Glutes, Adductors', 'equipment' => 'Barbell', 'type' => 'Strength'],
            ['name' => 'Good Morning', 'muscle_group' => 'Hamstrings', 'secondary_muscles' => 'Lower Back', 'equipment' => 'Barbell', 'type' => 'Strength'],

            // ── GLUTES ─────────────────────────────────────────────
            ['name' => 'Hip Thrust', 'muscle_group' => 'Glutes', 'secondary_muscles' => 'Hamstrings', 'equipment' => 'Barbell', 'type' => 'Strength'],
            ['name' => 'Glute Bridge', 'muscle_group' => 'Glutes', 'secondary_muscles' => 'Hamstrings', 'equipment' => 'Bodyweight', 'type' => 'Strength'],
            ['name' => 'Cable Kickback', 'muscle_group' => 'Glutes', 'secondary_muscles' => 'Hamstrings', 'equipment' => 'Cable', 'type' => 'Strength'],
            ['name' => 'Hip Abduction Machine', 'muscle_group' => 'Glutes', 'secondary_muscles' => null, 'equipment' => 'Machine', 'type' => 'Strength'],
            ['name' => 'Single Leg Hip Thrust', 'muscle_group' => 'Glutes', 'secondary_muscles' => 'Hamstrings', 'equipment' => 'Bodyweight', 'type' => 'Strength'],
            ['name' => 'Donkey Kick', 'muscle_group' => 'Glutes', 'secondary_muscles' => null, 'equipment' => 'Bodyweight', 'type' => 'Strength'],

            // ── CALVES ─────────────────────────────────────────────
            ['name' => 'Standing Calf Raise', 'muscle_group' => 'Calves', 'secondary_muscles' => null, 'equipment' => 'Machine', 'type' => 'Strength'],
            ['name' => 'Seated Calf Raise', 'muscle_group' => 'Calves', 'secondary_muscles' => null, 'equipment' => 'Machine', 'type' => 'Strength'],
            ['name' => 'Donkey Calf Raise', 'muscle_group' => 'Calves', 'secondary_muscles' => null, 'equipment' => 'Machine', 'type' => 'Strength'],
            ['name' => 'Single Leg Calf Raise', 'muscle_group' => 'Calves', 'secondary_muscles' => null, 'equipment' => 'Bodyweight', 'type' => 'Strength'],
            ['name' => 'Leg Press Calf Raise', 'muscle_group' => 'Calves', 'secondary_muscles' => null, 'equipment' => 'Machine', 'type' => 'Strength'],

            // ── CORE ───────────────────────────────────────────────
            ['name' => 'Plank', 'muscle_group' => 'Core', 'secondary_muscles' => 'Shoulders', 'equipment' => 'Bodyweight', 'type' => 'Strength'],
            ['name' => 'Side Plank', 'muscle_group' => 'Core', 'secondary_muscles' => null, 'equipment' => 'Bodyweight', 'type' => 'Strength'],
            ['name' => 'Crunch', 'muscle_group' => 'Core', 'secondary_muscles' => null, 'equipment' => 'Bodyweight', 'type' => 'Strength'],
            ['name' => 'Bicycle Crunch', 'muscle_group' => 'Core', 'secondary_muscles' => 'Obliques', 'equipment' => 'Bodyweight', 'type' => 'Strength'],
            ['name' => 'Russian Twist', 'muscle_group' => 'Core', 'secondary_muscles' => 'Obliques', 'equipment' => 'Bodyweight', 'type' => 'Strength'],
            ['name' => 'Leg Raise', 'muscle_group' => 'Core', 'secondary_muscles' => 'Hip Flexors', 'equipment' => 'Bodyweight', 'type' => 'Strength'],
            ['name' => 'Hanging Leg Raise', 'muscle_group' => 'Core', 'secondary_muscles' => 'Hip Flexors', 'equipment' => 'Bodyweight', 'type' => 'Strength'],
            ['name' => 'Ab Wheel Rollout', 'muscle_group' => 'Core', 'secondary_muscles' => 'Shoulders, Triceps', 'equipment' => 'Other', 'type' => 'Strength'],
            ['name' => 'Cable Crunch', 'muscle_group' => 'Core', 'secondary_muscles' => null, 'equipment' => 'Cable', 'type' => 'Strength'],
            ['name' => 'Decline Crunch', 'muscle_group' => 'Core', 'secondary_muscles' => null, 'equipment' => 'Bodyweight', 'type' => 'Strength'],
            ['name' => 'Mountain Climber', 'muscle_group' => 'Core', 'secondary_muscles' => 'Shoulders', 'equipment' => 'Bodyweight', 'type' => 'Cardio'],
            ['name' => 'Dead Bug', 'muscle_group' => 'Core', 'secondary_muscles' => null, 'equipment' => 'Bodyweight', 'type' => 'Strength'],
            ['name' => 'V-Up', 'muscle_group' => 'Core', 'secondary_muscles' => 'Hip Flexors', 'equipment' => 'Bodyweight', 'type' => 'Strength'],
            ['name' => 'Pallof Press', 'muscle_group' => 'Core', 'secondary_muscles' => null, 'equipment' => 'Cable', 'type' => 'Strength'],
            ['name' => 'Windshield Wiper', 'muscle_group' => 'Core', 'secondary_muscles' => 'Obliques', 'equipment' => 'Bodyweight', 'type' => 'Strength'],

            // ── FOREARMS ───────────────────────────────────────────
            ['name' => 'Wrist Curl', 'muscle_group' => 'Forearms', 'secondary_muscles' => null, 'equipment' => 'Barbell', 'type' => 'Strength'],
            ['name' => 'Reverse Wrist Curl', 'muscle_group' => 'Forearms', 'secondary_muscles' => null, 'equipment' => 'Barbell', 'type' => 'Strength'],
            ['name' => 'Farmer Walk', 'muscle_group' => 'Forearms', 'secondary_muscles' => 'Traps, Core', 'equipment' => 'Dumbbell', 'type' => 'Strength'],
            ['name' => 'Dead Hang', 'muscle_group' => 'Forearms', 'secondary_muscles' => 'Back', 'equipment' => 'Bodyweight', 'type' => 'Strength'],

            // ── CARDIO ─────────────────────────────────────────────
            ['name' => 'Treadmill Running', 'muscle_group' => 'Cardio', 'secondary_muscles' => null, 'equipment' => 'Machine', 'type' => 'Cardio'],
            ['name' => 'Cycling (Stationary)', 'muscle_group' => 'Cardio', 'secondary_muscles' => null, 'equipment' => 'Machine', 'type' => 'Cardio'],
            ['name' => 'Rowing Machine', 'muscle_group' => 'Cardio', 'secondary_muscles' => 'Back, Arms', 'equipment' => 'Machine', 'type' => 'Cardio'],
            ['name' => 'Elliptical', 'muscle_group' => 'Cardio', 'secondary_muscles' => null, 'equipment' => 'Machine', 'type' => 'Cardio'],
            ['name' => 'Jump Rope', 'muscle_group' => 'Cardio', 'secondary_muscles' => 'Calves, Shoulders', 'equipment' => 'Other', 'type' => 'Cardio'],
            ['name' => 'Stair Climber', 'muscle_group' => 'Cardio', 'secondary_muscles' => 'Glutes, Quads', 'equipment' => 'Machine', 'type' => 'Cardio'],
            ['name' => 'Battle Ropes', 'muscle_group' => 'Cardio', 'secondary_muscles' => 'Shoulders, Core', 'equipment' => 'Other', 'type' => 'Cardio'],
            ['name' => 'Box Jump', 'muscle_group' => 'Cardio', 'secondary_muscles' => 'Quads, Glutes', 'equipment' => 'Bodyweight', 'type' => 'Cardio'],
            ['name' => 'Burpee', 'muscle_group' => 'Cardio', 'secondary_muscles' => 'Chest, Core', 'equipment' => 'Bodyweight', 'type' => 'Cardio'],
            ['name' => 'Jumping Jack', 'muscle_group' => 'Cardio', 'secondary_muscles' => null, 'equipment' => 'Bodyweight', 'type' => 'Cardio'],
            ['name' => 'Sled Push', 'muscle_group' => 'Cardio', 'secondary_muscles' => 'Quads, Glutes', 'equipment' => 'Other', 'type' => 'Cardio'],
        ];

        DB::table('exercises')->insertOrIgnore($exercises);
    }
}
