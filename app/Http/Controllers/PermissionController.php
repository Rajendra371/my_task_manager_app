<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        $users = User::with('permissions')->get();
        
        return response()->json($users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'permissions' => $user->permissions ?: [
                    'can_create' => true,
                    'can_view' => true,
                    'can_edit' => true,
                    'can_update' => true,
                    'can_delete' => false
                ]
            ];
        }));
    }

    public function update(Request $request, User $user)
    {
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

        return response()->json(['message' => 'Permissions updated successfully']);
    }
}