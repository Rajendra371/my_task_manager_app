<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TaskController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $user = auth()->user();
        $perPage = $request->get('per_page', 10);
        $userId = $request->get('user_id');
        
        // Admins see all tasks, users see only their own
        if ($user->role === 'admin') {
            $query = Task::with('user');
            if ($userId) {
                $query->where('user_id', $userId);
            }
        } else {
            $query = Task::where('user_id', $user->id);
        }
            
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Task::class);
        
        try {
            $validated = $request->validate([
                'title' => 'required|min:3',
                'description' => 'nullable|max:500'
            ]);
            
            $task = Task::create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'user_id' => auth()->id(),
                'completed' => false,
            ]);
            
            return response()->json($task->load('user'), 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);
        return response()->json($task->load('user'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);
        
        try {
            $validated = $request->validate([
                'title' => 'sometimes|required|min:3|max:255',
                'description' => 'nullable|max:500',
                'completed' => 'sometimes|boolean'
            ]);
            
            $task->update($validated);
            return response()->json($task->load('user'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        $task->delete();
        return response()->json(['message' => 'Task deleted']);
    }
}