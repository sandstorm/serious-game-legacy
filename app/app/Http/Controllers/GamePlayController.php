<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Domain\CoreGameLogic\DrivingPorts\ForCoreGameLogic;
use Domain\CoreGameLogic\Dto\ValueObject\GameId;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Illuminate\Http\Request;

class GamePlayController extends Controller
{
    public function __construct(
        private readonly ForCoreGameLogic $coreGameLogic,
    )
    {
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, string $gameId, string $myselfId): \Illuminate\View\View
    {
        $gameId = new GameId($gameId);
        $myselfId = new PlayerId($myselfId);

        $this->coreGameLogic->startGameIfNotStarted($gameId);
        return view('game-play', [
            'gameId' => $gameId,
            'myself' => $myselfId,
        ]);
    }
}
