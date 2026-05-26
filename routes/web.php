<?php

use App\Http\Controllers\StudentRegistrationController;
use App\Http\Middleware\EnsureGoogleAuthConfigured;
use App\Http\Middleware\EnsureRegistrationAllowed;
use App\Http\Middleware\EnsureRegistrationSession;
use App\Http\Middleware\EnsureStudentNotRegistered;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('register.index');
});

Route::prefix('register')->name('register.')->group(function () {
    Route::get('/', [StudentRegistrationController::class, 'index'])->name('index');
    Route::get('/complete', [StudentRegistrationController::class, 'showComplete'])->name('complete');
    Route::get('/kiosk-required', [StudentRegistrationController::class, 'kioskRequired'])->name('kiosk-required');

    Route::middleware(EnsureRegistrationSession::class)->group(function () {
        Route::get('/already-registered', [StudentRegistrationController::class, 'alreadyRegistered'])
            ->name('already-registered');

        Route::middleware([EnsureRegistrationAllowed::class, EnsureStudentNotRegistered::class])->group(function () {
            Route::get('/continue', [StudentRegistrationController::class, 'continue'])->name('continue');
            Route::get('/questions', [StudentRegistrationController::class, 'showQuestions'])->name('questions');
            Route::post('/questions', [StudentRegistrationController::class, 'storeQuestions'])->name('questions.store');
            Route::get('/photo', [StudentRegistrationController::class, 'showPhoto'])->name('photo');
            Route::post('/photo', [StudentRegistrationController::class, 'storePhoto'])->name('photo.store');
            Route::get('/review', [StudentRegistrationController::class, 'showReview'])->name('review');
            Route::post('/complete', [StudentRegistrationController::class, 'complete'])->name('complete.submit');
        });
    });
});

Route::middleware(EnsureGoogleAuthConfigured::class)->group(function () {
    Route::get('/auth/google/redirect', [StudentRegistrationController::class, 'redirectToGoogle'])
        ->name('auth.google.redirect');

    Route::get('/auth/google/callback', [StudentRegistrationController::class, 'handleGoogleCallback'])
        ->name('auth.google.callback');
});
