<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Task;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalTasks = Task::count();
        $completedTasks = Task::where('completed', true)->count();
        $pendingTasks = Task::where('completed', false)->count();
        
        $recentTasks = Task::with('user')
            ->latest()
            ->take(10)
            ->get();
            
        $users = User::withCount('tasks')->get();
        
        return view('dashboard', compact(
            'totalUsers', 
            'totalTasks', 
            'completedTasks', 
            'pendingTasks', 
            'recentTasks', 
            'users'
        ));
    }
    
    public function tasks(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }
        
        $query = Task::with('user')->orderBy('created_at', 'desc');
        
        // Filter by user if specified
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        $tasks = $query->paginate(10);
        $users = User::all();
        
        return view('tasks', compact('tasks', 'users'));
    }
    
    public function users()
    {
        $users = User::withCount('tasks')->paginate(10);
        return view('users', compact('users'));
    }
}