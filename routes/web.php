<?php

use App\Http\Controllers\EmailController;
use App\Http\Controllers\FactoryVisitorController;
use App\Http\Controllers\GoogleSheetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Models\FormSubmission;
use App\Services\GoogleSheetsService;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Http\Controllers\GmailController;
use Google\Service\Gmail;
use Google\Client;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Route::get('/emails', [EmailController::class, 'index'])->name('emails.index');


// GET API google sheet update and send data to database
Route::middleware('auth')->group(function () {
    // GET API google sheet update and send data to database
    Route::post('/sync-google-sheets', [FactoryVisitorController::class, 'syncFromGoogleSheets'])
        ->name('google-sheets.sync');

    // Resource routes
    Route::resource('users', UserController::class);
    Route::resource('factory-visitors', FactoryVisitorController::class);
    Route::patch('/factory-visitors/{factoryVisitor}/status', [FactoryVisitorController::class, 'updateStatus'])
        ->name('factory-visitors.update-status');

    // Dashboard
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->middleware('verified')->name('dashboard');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// routes/web.php


Route::get('/gmail/auth', [GmailController::class, 'redirectToGoogle'])->name('gmail.auth');
Route::get('/gmail/callback', [GmailController::class, 'handleGoogleCallback'])->name('gmail.callback');
Route::get('/gmail/inbox', [GmailController::class, 'listEmails'])->middleware('google.auth')->name('gmail.inbox');
Route::get('/emails/{id}', [GmailController::class, 'showEmail'])->name('emails.show');

require __DIR__ . '/auth.php';
