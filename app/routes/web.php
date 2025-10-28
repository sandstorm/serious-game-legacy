<?php
declare(strict_types=1);

use App\ApplicationHelpers\ApplicationUnavailable;
use App\Http\Controllers\GamePlayController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:game', 'auth.session'])->group(function () {
    Route::get('/', [GamePlayController::class, 'index'])->name('game-play.index');
    Route::get('/quick-start/{players}', [GamePlayController::class, 'quickStart'])->name('game-play.quick-start');
    Route::get('/new-game', [GamePlayController::class, 'newGame'])->name('game-play.new-game');
    Route::post('/new-game', [GamePlayController::class, 'createGame'])->name('game-play.create-game');
    Route::get('/play/{gameId}', [GamePlayController::class, 'playerLinks'])->name('game-play.player-links');
});

// login and auth routes for players
Route::get('/login', [LoginController::class, 'login'])->name('login');
Route::post('/login', [LoginController::class, 'authenticate']);

// the game play route, no auth middleware here, auth is done inside the controller
Route::get('/play/{gameId}/{playerId}', [GamePlayController::class, 'game'])->name('game-play.game');

if (app()->isLocal()) {
    Route::get('/preview-application-unavailable', function () {
        return new ApplicationUnavailable();
    });
}
