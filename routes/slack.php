<?php

use App\Http\Controllers\SlackInteractionController;
use App\Http\Middleware\VerifySlackSignature;
use Illuminate\Support\Facades\Route;

Route::post('/slack/interactions', [SlackInteractionController::class, 'handle'])
    ->middleware([VerifySlackSignature::class, 'throttle:slack-interactions'])
    ->name('slack.interactions');
