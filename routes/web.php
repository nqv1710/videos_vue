<?php

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

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// GET API google sheet update and send data to database
Route::post('/sync-google-sheets', [FactoryVisitorController::class, 'syncFromGoogleSheets'])->name('google-sheets.sync');

// Route::post('/sync-google-sheets', [GoogleSheetController::class, 'syncData'])
//     // ->middleware(['auth'])
//     ->name('google-sheets.sync');

Route::resource('users', UserController::class);
Route::resource('factory-visitors', FactoryVisitorController::class);

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Add route to view submissions
Route::get('/form-submissions', function () {
    $submissions = FormSubmission::latest()->paginate(10);
    return Inertia::render('FormSubmissions/Index', [
        'submissions' => $submissions
    ]);
})->middleware(['auth'])->name('form-submissions.index');

require __DIR__.'/auth.php';
