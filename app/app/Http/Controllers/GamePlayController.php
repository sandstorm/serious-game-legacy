<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Livewire\Dto\Games;
use App\Models\Game;
use App\Models\Player;
use Domain\CoreGameLogic\DrivingPorts\ForCoreGameLogic;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartPreGame;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\GameId;
use Domain\CoreGameLogic\PlayerId;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
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
        // get games sorted by created at desc
        $games = $loggedInPlayer?->games()->orderBy('created_at', 'desc')->get() ?? [];

        $gamesDto = [];
        foreach ($games as $game) {
            $gameEvents = $this->coreGameLogic->getGameEvents(GameId::fromString((string) $game->id));
            $gamesDto[] = new Games(
                game: $game,
                playerNames: array_values(
                    array_map(function ($playerWithnameAndLebensziel) { return $playerWithnameAndLebensziel->name ?? null; }, PreGameState::playersWithNameAndLebensziel($gameEvents))
                ),
                isInGamePhase: !PreGameState::isInPreGamePhase($gameEvents),
            );
        }

        return view('controllers.gameplay.index', [
            'games' => $gamesDto,
            'player' => $loggedInPlayer,
        ]);
    }

    /**
     * @param Request $request
     * @return View
     */
    public function newGame(Request $request): View
    {
        return view('controllers.gameplay.new');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function createGame(Request $request): RedirectResponse
    {
        $loggedInPlayer = $request->user('game');
        if ($loggedInPlayer === null || !$loggedInPlayer->can_create_games) {
            abort(403);
        }

        $validated = Validator::make($request->all(), [
            'numberOfPlayers' => 'required|integer|gte:2',
        ])->validate();

        $gameId = $this->startGame($loggedInPlayer, intval($validated['numberOfPlayers']));

        return redirect()->route('game-play.player-links', [
            'gameId' => $gameId
        ]);
    }

    /**
     * @param Request $request
     * @param string $gameId
     * @return View|RedirectResponse
     */
    public function playerLinks(Request $request, string $gameId): View|RedirectResponse
    {
        $gameId = GameId::fromString($gameId);
        if (Game::find($gameId->value) === null) {
            abort(404);
        }

        $gameEvents = $this->coreGameLogic->getGameEvents($gameId);
        return view('controllers.gameplay.player-links', [
            'gameId' => $gameId,
            'playerIds' => PreGameState::playerIds($gameEvents),
            'player' => $request->user('game'),
        ]);
    }

    /**
     * @param Request $request
     * @param string $gameId
     * @param string $playerId
     * @return View|RedirectResponse
     */
    public function game(Request $request, string $gameId, string $playerId): View|RedirectResponse
    {
        $gameId = GameId::fromString($gameId);
        if (!$this->coreGameLogic->hasGame($gameId)) {
            return redirect()->route('game-play.new-game');
        }

        $playerId = PlayerId::fromString($playerId);
        $loggedInPlayer = $request->user('game');
        $game = Game::find($gameId->value);

        if ($game === null) {
            abort(404);
        }

        // check if player is part of the game
        $gameEvents = $this->coreGameLogic->getGameEvents($gameId);
        $players = PreGameState::playerIds($gameEvents);
        $isPlayerInGame = false;
        foreach ($players as $playerInGame) {
            if ($playerInGame->equals($playerId)) {
                $isPlayerInGame = true;
                break;
            }
        }

        if (!$isPlayerInGame) {
            abort(403);
        }

        // check if logged in player matches player id when the game is not created by a player
        if (!$game->isCreatedByPlayer() && $loggedInPlayer !== null && $playerId->value !== $loggedInPlayer->id) {
            abort(403);
        }

        return view('controllers.gameplay.game-play', [
            'gameId' => $gameId,
            'myself' => $playerId,
        ]);
    }

    /**
     * @param Player $player
     * @param int $numberOfPlayers
     * @return GameId
     */
    private function startGame(Player $player, int $numberOfPlayers): GameId
    {
        // create game in database
        $game = new Game();
        /** @phpstan-ignore assign.propertyType */
        $game->id = Str::ulid();
        $game->creator()->associate($player);
        $game->course()->associate($player->courses()->first());
        $game->save();
        $game->players()->attach($player);

        // start game in core game logic
        $gameId = GameId::fromString((string) $game->id);
        $playerIds = [
            /** @phpstan-ignore argument.type */
            PlayerId::fromString($player->id),
        ];
        // add random player ids for the other players
        for ($i = 1; $i < $numberOfPlayers; $i++) {
            $playerIds[] = PlayerId::unique();
        }
        $this->coreGameLogic->handle($gameId, StartPreGame::create(
            numberOfPlayers: $numberOfPlayers
        )->withFixedPlayerIds(
            $playerIds
        ));

        return $gameId;
    }
}
