<?php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\PermissionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/login', fn() => response()->json(['message' => 'Unauthenticated'], 401))->name('login');
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->get('/user', fn(Request $request) => $request->user());
Route::middleware('auth:sanctum')->get('/users', function(Request $request) {
    if ($request->user()->role !== 'admin') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    return \App\Models\User::select('id', 'name', 'email', 'role')->get();
});

Route::middleware('auth:sanctum')->get('/permissions', [PermissionController::class, 'index']);
Route::middleware('auth:sanctum')->put('/permissions/{user}', [PermissionController::class, 'update']);

Route::middleware('auth:sanctum')->get('/tasks', [TaskController::class, 'index']);
Route::middleware('auth:sanctum')->post('/tasks', [TaskController::class, 'store']);
Route::middleware('auth:sanctum')->get('/tasks/{task}', [TaskController::class, 'show']);
Route::middleware('auth:sanctum')->put('/tasks/{task}', [TaskController::class, 'update']);
Route::middleware('auth:sanctum')->delete('/tasks/{task}', [TaskController::class, 'destroy']);
