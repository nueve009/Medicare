<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
                    'id' => $user->user_id, // Mapping the DB user_id to the React Native 'id'
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'role' => $user->role 
                ]
            ], 200);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }
}