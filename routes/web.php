<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/app', function () {
    return view('app');
});

// Admin routes
Route::get('/admin/login', [AdminController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])->withoutMiddleware(['csrf']);
Route::get('/admin/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('admin.login')->with('success', 'Logged out successfully.');
})->name('admin.logout.get');

// Dashboard route - check auth first
Route::get('/dashboard', function () {
    if (!Auth::check()) {
        return redirect()->route('admin.login')->with('error', 'Please login first.');
    }
    
    if (Auth::user()->role !== 'admin') {
        return redirect()->route('admin.login')->with('error', 'Admin access required.');
    }
    
    return app(DashboardController::class)->index();
})->name('admin.dashboard');

Route::get('/dashboard/tasks', [DashboardController::class, 'tasks']);

Route::get('/dashboard/users', function () {
    if (!Auth::check() || Auth::user()->role !== 'admin') {
        return redirect()->route('admin.login');
    }
    return app(DashboardController::class)->users();
});

Route::get('/dashboard/permissions', function () {
    if (!Auth::check() || Auth::user()->role !== 'admin') {
        return redirect()->route('admin.login');
    }
    return view('permissions');
});

Route::get('/dashboard/api/permissions', [\App\Http\Controllers\PermissionController::class, 'index'])->middleware('web');
Route::put('/dashboard/api/permissions/{user}', [\App\Http\Controllers\PermissionController::class, 'update'])->middleware('web');

// Dashboard Task API routes
Route::put('/dashboard/api/tasks/{task}', [\App\Http\Controllers\Api\TaskController::class, 'update'])->middleware('web');
Route::delete('/dashboard/api/tasks/{task}', [\App\Http\Controllers\Api\TaskController::class, 'destroy'])->middleware('web');
