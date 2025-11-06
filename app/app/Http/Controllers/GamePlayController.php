<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
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
        if ($loggedInPlayer === null || !$loggedInPlayer->can_create_games) {
            abort(403);
        }

        $validated = Validator::make($request->all(), [
            'numberOfPlayers' => 'required|integer|gte:1',
        ])->validate();

        $gameId = $this->startGame($loggedInPlayer, intval($validated['numberOfPlayers']));

        return redirect()->route('game-play.player-links', [
            'gameId' => $gameId
        ]);
    }

    // TODO remove this method in the future - only for quick testing
    public function quickStart(Request $request, int $amountOfPlayers): RedirectResponse
    {
        $loggedInPlayer = $request->user('game');
        if ($loggedInPlayer === null) {
            abort(403);
        }
        $gameId = $this->startGame($loggedInPlayer, $amountOfPlayers);

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

        return redirect()->route('game-play.game', [
            'gameId' => $gameId->value,
            'playerId' => $playerIds[0]->value,
        ]);
    }

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
        /** @phpstan-ignore method.nonObject */
        $gameId = GameId::fromString($game->id->toString());
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
