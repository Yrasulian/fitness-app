<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'age' => 'nullable|integer',
            'gender' => 'nullable|in:M,F,Other',
            'goal' => 'nullable|string',
            'experience_level' => 'nullable|in:Beginner,Intermediate,Advanced',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'age' => $validated['age'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'goal' => $validated['goal'] ?? null,
            'experience_level' => $validated['experience_level'] ?? 'Beginner',
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
            'token' => $this->generateToken($user),
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $this->generateToken($user),
        ], 200);
    }

    public function logout(Request $request)
    {
        return response()->json([
            'message' => 'Logout successful',
        ], 200);
    }

    public function me(Request $request)
    {
        $user = auth()->user();
        return response()->json($user);
    }

    protected function generateToken($user)
    {
        $payload = [
            'iss' => config('app.url'),
            'sub' => $user->id,
            'iat' => time(),
            'exp' => time() + (config('jwt.ttl') * 60),
        ];

        return JWT::encode($payload, config('jwt.secret'), config('jwt.algorithm'));
    }
}
