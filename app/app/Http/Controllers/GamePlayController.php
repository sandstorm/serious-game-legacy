<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Domain\CoreGameLogic\DrivingPorts\ForCoreGameLogic;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartPreGame;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\GameId;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Session\Store as SessionStore;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class GamePlayController extends Controller
{
    public function __construct(
        private readonly ForCoreGameLogic $coreGameLogic,
    )
    {
    }

    public function newGame(Request $request): View
    {
        return view('controllers.gameplay.new');
    }

    public function playerLinks(Request $request, SessionStore $session, string $gameId): View
    {
        $gameId = GameId::fromString($gameId);
        $validated = Validator::make($request->all(), [
            'numberOfPlayers' => 'required|integer|gte:1',
        ])->validate();
        if (!$this->coreGameLogic->hasGame($gameId)) {
            $this->coreGameLogic->handle($gameId, StartPreGame::create(
                numberOfPlayers: intval($validated['numberOfPlayers'])
            ));
            $session->flash('success', 'Game started');
        }

        $gameStream = $this->coreGameLogic->getGameEvents($gameId);
        return view('controllers.gameplay.player-links', [
            'gameId' => $gameId,
            'playerIds' => PreGameState::playerIds($gameStream)
        ]);
    }

    public function game(Request $request, string $gameId, string $myselfId): View|RedirectResponse
    {
        $gameId = GameId::fromString($gameId);
        $myselfId = PlayerId::fromString($myselfId);

        if (!$this->coreGameLogic->hasGame($gameId)) {
            // TODO: Flash
            return redirect()->route('game-play.new');
        }

        return view('controllers.gameplay.game-play', [
            'gameId' => $gameId,
            'myself' => $myselfId,
        ]);
    }
}
