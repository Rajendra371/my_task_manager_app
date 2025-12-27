<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        $currentUser = auth()->user();
        
        if ($currentUser->role === 'admin') {
            // Admin can see all users and their permissions
            $users = User::with('permissions')->get();
            
            return response()->json($users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'permissions' => $user->permissions ? [
                        'can_create' => $user->permissions->can_create,
                        'can_view' => $user->permissions->can_view,
                        'can_edit' => $user->permissions->can_edit,
                        'can_update' => $user->permissions->can_update,
                        'can_delete' => $user->permissions->can_delete
                    ] : [
                        'can_create' => true,
                        'can_view' => true,
                        'can_edit' => true,
                        'can_update' => true,
                        'can_delete' => false
                    ]
                ];
            }))->header('Access-Control-Allow-Origin', '*')
              ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
              ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        } else {
            // Regular users can only see their own permissions
            return response()->json([[
                'id' => $currentUser->id,
                'name' => $currentUser->name,
                'email' => $currentUser->email,
                'role' => $currentUser->role,
                'permissions' => $currentUser->permissions ? [
                    'can_create' => $currentUser->permissions->can_create,
                    'can_view' => $currentUser->permissions->can_view,
                    'can_edit' => $currentUser->permissions->can_edit,
                    'can_update' => $currentUser->permissions->can_update,
                    'can_delete' => $currentUser->permissions->can_delete
                ] : [
                    'can_create' => true,
                    'can_view' => true,
                    'can_edit' => true,
                    'can_update' => true,
                    'can_delete' => false
                ]
            ]])->header('Access-Control-Allow-Origin', '*')
              ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
              ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        }
    }

    public function update(Request $request, User $user)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'can_create' => 'sometimes|boolean',
            'can_view' => 'sometimes|boolean',
            'can_edit' => 'sometimes|boolean',
            'can_update' => 'sometimes|boolean',
            'can_delete' => 'sometimes|boolean'
        ]);

        UserPermission::updateOrCreate(
            ['user_id' => $user->id],
            $request->only(['can_create', 'can_view', 'can_edit', 'can_update', 'can_delete'])
        );

        return response()->json(['message' => 'Permissions updated successfully'])
                        ->header('Access-Control-Allow-Origin', '*')
                        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }
}