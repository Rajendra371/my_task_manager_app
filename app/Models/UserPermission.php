<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    protected $fillable = [
        'user_id',
        'can_create',
        'can_view', 
        'can_edit',
        'can_update',
        'can_delete'
    ];

    protected $casts = [
        'can_create' => 'boolean',
        'can_view' => 'boolean',
        'can_edit' => 'boolean', 
        'can_update' => 'boolean',
        'can_delete' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
