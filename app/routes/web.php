<?php
declare(strict_types=1);

use App\ApplicationHelpers\ApplicationUnavailable;
use App\Http\Controllers\GamePlayController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:game', 'auth.session'])->group(function () {
    Route::get('/', function () {
        return view('controllers.welcome');
    });

    Route::get('/quick-start/{players}', [GamePlayController::class, 'quickStart'])->name('game-play.quick-start');

    Route::get('/play', [GamePlayController::class, 'newGame'])->name('game-play.new-game');
    Route::get('/play/{gameId}', [GamePlayController::class, 'playerLinks'])->name('game-play.player-links');
    Route::get('/play/{gameId}/{playerId}', [GamePlayController::class, 'game'])->name('game-play.game');
});

// login and auth routes for players
Route::get('/login', [LoginController::class, 'login'])->name('login');
Route::post('/login', [LoginController::class, 'authenticate']);

if (app()->isLocal()) {
    Route::get('/preview-application-unavailable', function () {
        return new ApplicationUnavailable();
    });
}
