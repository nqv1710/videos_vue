<?php

use App\Http\Controllers\BitrixLoginController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


Route::get('/bitrix/login', [BitrixLoginController::class, 'redirectToBitrix'])->name('bitrix.login');
Route::get('/bitrix/callback', [BitrixLoginController::class, 'handleCallback'])->name('bitrix.callback');

Route::middleware('auth')->group(function () {
    // Trang chính
    Route::get('/', function () {
        return Inertia::render('Dashboard', [
            'canLogin'       => Route::has('login'),
            'canRegister'    => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion'     => PHP_VERSION,
        ]);
    });

    Route::get('/dashboard', fn () => Inertia::render('Dashboard'))
        ->name('dashboard');

    // Admin routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', fn () => Inertia::render('Admin/Index'))->name('index');
        Route::get('/list-comment/{id}/{user_id}', fn ($id, $user_id) => 
            Inertia::render('Admin/ListComment', compact('id', 'user_id'))
        )->name('list-comment');
    });

    // Profile routes
    Route::controller(ProfileController::class)->prefix('profile')->name('profile.')->group(function () {
        Route::get('/', 'edit')->name('edit');
        Route::patch('/', 'update')->name('update');
        Route::delete('/', 'destroy')->name('destroy');
    });
});

// routes/web.php
require __DIR__ . '/auth.php';
