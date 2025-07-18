<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Domain\CoreGameLogic\DrivingPorts\ForCoreGameLogic;
use Domain\CoreGameLogic\Feature\Initialization\Command\SelectLebensziel;
use Domain\CoreGameLogic\Feature\Initialization\Command\SetNameForPlayer;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartGame;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartPreGame;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
use Domain\CoreGameLogic\GameId;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Lebensziel\ValueObject\LebenszielId;
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

    public function quickStart(Request $request): RedirectResponse
    {
        $gameId = GameId::random();
        $this->coreGameLogic->handle($gameId, StartPreGame::create(
            numberOfPlayers: 2
        ));

        $gameEvents = $this->coreGameLogic->getGameEvents($gameId);
        $playerIds = PreGameState::playerIds($gameEvents);

        for ($index = 0; $index < 2; $index++) {
            $this->coreGameLogic->handle($gameId, new SetNameForPlayer(
                playerId: $playerIds[$index],
                name: 'Player ' . $index,
            ));
            $this->coreGameLogic->handle($gameId, new SelectLebensziel(
                playerId: $playerIds[$index],
                lebenszielId: LebenszielId::create($index % 2 + 1),
            ));

        }

        $this->coreGameLogic->handle($gameId, StartGame::create());
        $this->coreGameLogic->handle($gameId, ChangeKonjunkturphase::create());

        // redirect to the game page
        return redirect()->route('game-play.game', [
            'gameId' => $gameId->value,
            'myselfId' => $playerIds[0]->value,
        ]);
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

        $gameEvents = $this->coreGameLogic->getGameEvents($gameId);
        return view('controllers.gameplay.player-links', [
            'gameId' => $gameId,
            'playerIds' => PreGameState::playerIds($gameEvents)
        ]);
    }

    public function game(Request $request, string $gameId, string $myselfId): View|RedirectResponse
    {
        $gameId = GameId::fromString($gameId);
        $myselfId = PlayerId::fromString($myselfId);

        if (!$this->coreGameLogic->hasGame($gameId)) {
            return redirect()->route('game-play.new');
        }

        return view('controllers.gameplay.game-play', [
            'gameId' => $gameId,
            'myself' => $myselfId,
        ]);
    }
}
