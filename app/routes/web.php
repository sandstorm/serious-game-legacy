<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

if (app()->isLocal()) {
    Route::get('/preview-application-unavailable', function () {
        return new App\Mail\ApplicationUnavailable();
    });

    Route::get('/play/{playId}', \App\Http\Controllers\GamePlayController::class);
}
