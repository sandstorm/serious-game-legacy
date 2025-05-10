<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Domain\CoreGameLogic\DrivingPorts\ForCoreGameLogic;
use Domain\CoreGameLogic\Dto\ValueObject\GameId;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartGame;
use Illuminate\Http\Request;

class GamePlayController extends Controller
{
    public function __construct(
        private readonly ForCoreGameLogic $coreGameLogic,
    ) {
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, string $gameId, string $myselfId): \Illuminate\View\View
    {
        $gameId = new GameId($gameId);
        $myselfId = new PlayerId($myselfId);

        $p1 = new PlayerId('p1');
        $p2 = new PlayerId('p2');

        if (!$this->coreGameLogic->hasGame($gameId)) {
            $this->coreGameLogic->handle($gameId, new StartGame(
                playerOrdering: [$p1, $p2],
            ));
        }

        return view('game-play', [
            'gameId' => $gameId,
            'myself' => $myselfId,
        ]);
    }
}
