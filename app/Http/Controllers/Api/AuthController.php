<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request) 
    {
        $validated = $request->validate([
            'name' => 'required', 
            'email' => 'required|email|unique:users', 
            'password' => 'required|min:8', 
            'role' => 'required|in:user,admin'
        ]);
        
        $user = User::create([
            ...$validated, 
            'password' => bcrypt($validated['password'])
        ]);
        
        $token = $user->createToken('auth_token')->plainTextToken;
        
        return response()->json(['token' => $token, 'user' => $user]);
    }
    
    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);
            
            if (Auth::attempt($validated)) {
                $user = Auth::user();
                $token = $user->createToken('auth_token')->plainTextToken;
                
                return response()->json([
                    'token' => $token, 
                    'user' => $user
                ])->header('Access-Control-Allow-Origin', '*');
            }
            
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401)->header('Access-Control-Allow-Origin', '*');
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Server error: ' . $e->getMessage()
            ], 500)->header('Access-Control-Allow-Origin', '*');
        }
    }
    
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
