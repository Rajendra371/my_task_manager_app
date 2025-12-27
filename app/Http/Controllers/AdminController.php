<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AdminController extends Controller
{
    public function showLogin()
    {
        // If already logged in as admin, redirect to dashboard
        if (Auth::check() && Auth::user()->role === 'admin') {
            return redirect('/dashboard');
        }
        
        return view('admin-login');
    }
    
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Check if user is admin
            if ($user->role !== 'admin') {
                Auth::logout();
                return back()->with('error', 'Access denied. Admin privileges required.');
            }
            
            $request->session()->regenerate();
            return redirect('/dashboard');
        }
        
        return back()->with('error', 'Invalid credentials.');
    }
    
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('admin.login')->with('success', 'Logged out successfully.');
    }
}