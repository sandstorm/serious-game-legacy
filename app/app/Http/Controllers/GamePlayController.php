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
use Domain\CoreGameLogic\Feature\Spielzug\Command\StartKonjunkturphaseForPlayer;
use Domain\CoreGameLogic\GameId;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Lebensziel\ValueObject\LebenszielId;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class GamePlayController extends Controller
{
    public function __construct(
        private readonly ForCoreGameLogic $coreGameLogic,
    )
    {
    }

    /**
     * List all the games of the logged in player
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $loggedInPlayer = $request->user('game');
        $games = $loggedInPlayer?->games()->get() ?? [];

        // TODO DTO for games with additional info (like game started, current phase, etc.)
        // $this->coreGameLogic->getGameEvents($this->gameId)

        return view('controllers.gameplay.index', [
            'games' => $games,
            'player' => $loggedInPlayer,
        ]);
    }

    public function newGame(Request $request): View
    {
        return view('controllers.gameplay.new');
    }

    // TODO game creation is not done at this point
    // * game should be created in the database and the player associated with it
    // * game needs special flag to indicate its a player created game
    // * only players with flag to create own games should be allowed to create games
    // * create link for other people to join the game
    // * disable auth for joining the game via link
    public function createGame(Request $request): RedirectResponse
    {
        $loggedInPlayer = $request->user('game');
        if ($loggedInPlayer === null) {
            abort(403);
        }

        $gameId = GameId::random();

        $validated = Validator::make($request->all(), [
            'numberOfPlayers' => 'required|integer|gte:1',
        ])->validate();

        $playerIds = [
            /** @phpstan-ignore argument.type */
            PlayerId::fromString($loggedInPlayer->id),
        ];
        // add random player ids for the other players
        for ($i = 1; $i < intval($validated['numberOfPlayers']); $i++) {
            $playerIds[] = PlayerId::unique();
        }

        $this->coreGameLogic->handle($gameId, StartPreGame::create(
            numberOfPlayers: intval($validated['numberOfPlayers'])
        )->withFixedPlayerIds(
            $playerIds
        ));

        return redirect()->route('game-play.player-links', [
            'gameId' => $gameId->value,
        ]);
    }

    // TODO remove this method in the future - only for quick testing
    public function quickStart(Request $request, int $amountOfPlayers): RedirectResponse
    {
        $gameId = GameId::random();
        $this->coreGameLogic->handle($gameId, StartPreGame::create(
            numberOfPlayers: $amountOfPlayers
        ));

        $gameEvents = $this->coreGameLogic->getGameEvents($gameId);
        $playerIds = PreGameState::playerIds($gameEvents);

        for ($index = 0; $index < $amountOfPlayers; $index++) {
            $this->coreGameLogic->handle($gameId, new SetNameForPlayer(
                playerId: $playerIds[$index],
                name: 'Player ' . $index + 1,
            ));
            $this->coreGameLogic->handle($gameId, new SelectLebensziel(
                playerId: $playerIds[$index],
                lebenszielId: LebenszielId::create($index % 2 + 1),
            ));
        }

        $this->coreGameLogic->handle($gameId, StartGame::create());
        $this->coreGameLogic->handle($gameId, ChangeKonjunkturphase::create());

        for ($index = 0; $index < $amountOfPlayers; $index++) {
            $this->coreGameLogic->handle($gameId, StartKonjunkturphaseForPlayer::create($playerIds[$index]));
        }

        // redirect to the game page
        return redirect()->route('game-play.game', [
            'gameId' => $gameId->value,
            'playerId' => $playerIds[0]->value,
        ]);
    }

    public function playerLinks(Request $request, string $gameId): View
    {
        $gameId = GameId::fromString($gameId);
        $gameEvents = $this->coreGameLogic->getGameEvents($gameId);
        return view('controllers.gameplay.player-links', [
            'gameId' => $gameId,
            'playerIds' => PreGameState::playerIds($gameEvents)
        ]);
    }

    public function game(Request $request, string $gameId, string $playerId): View|RedirectResponse
    {
        $gameId = GameId::fromString($gameId);
        $playerId = PlayerId::fromString($playerId);
        $loggedInPlayer = $request->user('game');

        if ($loggedInPlayer !== null && $playerId->value !== $loggedInPlayer->id) {
            abort(403);
        }

        if (!$this->coreGameLogic->hasGame($gameId)) {
            return redirect()->route('game-play.new-game');
        }

        return view('controllers.gameplay.game-play', [
            'gameId' => $gameId,
            'myself' => $playerId,
        ]);
    }
}
