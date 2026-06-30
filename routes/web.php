<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TimeLogController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Support\Facades\Route;

// ── Root redirect ──────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));

// ── Auth routes (guests only) ──────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.post');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ── Authenticated routes ───────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [WorkspaceController::class, 'index'])->name('dashboard');

    // Workspaces
    Route::post('/workspaces', [WorkspaceController::class, 'store'])->name('workspaces.store');

    // Workspace-scoped routes — protected by EnsureUserInWorkspace middleware
    Route::prefix('/workspaces/{workspace}')
        ->middleware('workspace.member')
        ->name('workspaces.')
        ->group(function () {

            // Workspace detail
            Route::get('/', [WorkspaceController::class, 'show'])->name('show');

            // Projects
            Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
            Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');

            Route::prefix('/projects/{project}')->name('projects.')->group(function () {
                Route::get('/', [ProjectController::class, 'show'])->name('show');
                Route::patch('/', [ProjectController::class, 'update'])->name('update');

                // Tasks
                Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
                Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
                Route::patch('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
                Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');

                // Time Logs
                Route::post('/tasks/{task}/time-logs', [TimeLogController::class, 'store'])->name('tasks.time-logs.store');
            });
        });
});
