<?php
declare(strict_types=1);

use App\ApplicationHelpers\ApplicationUnavailable;
use App\Http\Controllers\GamePlayController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('controllers.welcome');
});

Route::get('/quick-start', [GamePlayController::class, 'quickStart'])->name('game-play.quick-start');

Route::get('/play', [GamePlayController::class, 'newGame'])->name('game-play.new-game');
Route::get('/play/{gameId}', [GamePlayController::class, 'playerLinks'])->name('game-play.player-links');
Route::get('/play/{gameId}/{myselfId}', [GamePlayController::class, 'game'])->name('game-play.game');

if (app()->isLocal()) {
    Route::get('/preview-application-unavailable', function () {
        return new ApplicationUnavailable();
    });
}
