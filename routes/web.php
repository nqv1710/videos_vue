<?php

use App\Http\Controllers\BitrixLoginController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return Inertia::render('Dashboard', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/bitrix/login', [BitrixLoginController::class, 'redirectToBitrix']);
Route::get('/bitrix/callback', [BitrixLoginController::class, 'handleCallback']);

Route::get('/admin', function () {
    return Inertia::render('Admin/Index');
})->middleware('auth')->name('admin');

Route::get('/admin/list-comment/{id}/{user_id}', function ($id, $user_id) {
    return Inertia::render('Admin/ListComment', [
        'id' => $id,
        'user_id' => $user_id,
    ]);
})->middleware('auth')->name('admin.list-comment');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {

    // Dashboard


    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// routes/web.php
require __DIR__ . '/auth.php';
