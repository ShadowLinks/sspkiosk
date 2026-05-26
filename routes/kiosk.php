<?php

use App\Http\Controllers\KioskEnrollmentController;
use App\Http\Controllers\KioskHeartbeatController;
use App\Http\Controllers\KioskResetController;
use App\Http\Controllers\KioskSessionController;
use App\Http\Middleware\EnsureKioskWebSession;
use App\Http\Middleware\EnsureResetPasswordModeConfigured;
use App\Http\Middleware\ValidateKioskRequest;
use Illuminate\Support\Facades\Route;

Route::prefix('kiosk')->group(function () {
    Route::post('/enroll', [KioskEnrollmentController::class, 'enroll'])
        ->middleware('throttle:kiosk-enroll')
        ->name('kiosk.enroll');

    Route::middleware(ValidateKioskRequest::class)->group(function () {
        Route::post('/heartbeat', [KioskHeartbeatController::class, 'store'])->name('kiosk.heartbeat');
    });

    Route::middleware(['web', ValidateKioskRequest::class])->group(function () {
        Route::post('/bind-session', [KioskSessionController::class, 'bind'])->name('kiosk.bind-session');
    });

    Route::middleware(['web', EnsureKioskWebSession::class, EnsureResetPasswordModeConfigured::class])
        ->prefix('reset')
        ->name('kiosk.reset.')
        ->group(function () {
            Route::get('/', [KioskResetController::class, 'index'])->name('index');
            Route::get('/unavailable', [KioskResetController::class, 'unavailable'])->name('unavailable');
            Route::get('/confirm', [KioskResetController::class, 'confirm'])->name('confirm');
            Route::get('/photo', [KioskResetController::class, 'showPhoto'])->name('photo');
            Route::get('/questions', [KioskResetController::class, 'showQuestions'])->name('questions');
            Route::get('/password', [KioskResetController::class, 'showPassword'])->name('password');
            Route::get('/pending-password/{resetRequest}', [KioskResetController::class, 'pendingPassword'])->name('pending-password');
            Route::get('/submitted/{resetRequest}', [KioskResetController::class, 'submitted'])->name('submitted');
        });

    Route::middleware(['web', EnsureKioskWebSession::class, EnsureResetPasswordModeConfigured::class, ValidateKioskRequest::class])
        ->prefix('reset')
        ->name('kiosk.reset.')
        ->group(function () {
            Route::post('/lookup', [KioskResetController::class, 'lookup'])
                ->middleware('throttle:kiosk-reset-lookup')
                ->name('lookup');
            Route::post('/photo', [KioskResetController::class, 'storePhoto'])->name('photo.store');
            Route::post('/submit', [KioskResetController::class, 'submit'])->name('submit');
            Route::post('/password', [KioskResetController::class, 'storePassword'])->name('password.store');
        });
});
