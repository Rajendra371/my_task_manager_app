<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user)
    {
        // Check user permissions - allow by default
        $permissions = $user->permissions;
        if (!$permissions) return true;
        return $permissions->can_view !== false;
    }

    public function view(User $user, Task $task)
    {
        // Check permissions first - allow by default
        $permissions = $user->permissions;
        if ($permissions && $permissions->can_view === false) return false;
        
        // Admins can view all tasks, users can only view their own
        return $user->role === 'admin' || $user->id === $task->user_id;
    }

    public function create(User $user)
    {
        // Check user permissions - allow by default
        $permissions = $user->permissions;
        if (!$permissions) return true;
        return $permissions->can_create !== false;
    }

    public function update(User $user, Task $task)
    {
        // Check permissions first - allow by default
        $permissions = $user->permissions;
        if ($permissions && $permissions->can_update === false) return false;
        
        // Admins can update all tasks, users can only update their own
        return $user->role === 'admin' || $user->id === $task->user_id;
    }

    public function delete(User $user, Task $task)
    {
        // Check permissions first - deny by default for delete
        $permissions = $user->permissions;
        if (!$permissions) return false;
        if ($permissions->can_delete !== true) return false;
        
        // Admins can delete all tasks, users can only delete their own
        return $user->role === 'admin' || $user->id === $task->user_id;
    }
}