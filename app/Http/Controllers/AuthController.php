<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();
            
            // Generate an API token using Laravel Sanctum
            $token = $user->createToken('mobile-app')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'token' => $token, // Your React Native app needs this!
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'role' => $user->role,
                    'prc_id' => $user->prc_id,
                    'clinics' => $user->clinics->map(fn($c) => [
                    'id' => $c->id,
                    'clinic_name' => $c->clinic_name,
                    ])
                ]
                
            ], 200);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function register(Request $request) {
        // Ensure only admins can register new users
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Only admins can register users.'], 403);
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'role' => 'required|in:doctor,assistant',
            'prc_id' => 'required_if:role,doctor|nullable|string|max:255',
            'specialization' => 'required_if:role,doctor|nullable|string|max:255',
            // SIMPLIFIED: Since admins aren't created here, clinics are ALWAYS required
            'clinic_ids' => 'required|array|min:1',
            'clinic_ids.*' => 'exists:clinics,id',
        ]);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'prc_id' => $validated['prc_id'] ?? null,
            'specialization' => $validated['specialization'] ?? null,
        ]);

        $user->clinics()->attach($validated['clinic_ids']);

        return response()->json([
            'message' => 'User successfully registered',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'role' => $user->role,
                'prc_id' => $user->prc_id,
            ]
        ], 201);
    }

    public function logout(Request $request) {
        // Actively destroy the current Sanctum token
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully'], 200);
    }
}