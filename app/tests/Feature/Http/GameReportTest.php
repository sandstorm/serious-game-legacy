<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Models\Game;
use App\Models\Player;
use Domain\CoreGameLogic\DrivingPorts\ForCoreGameLogic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);
beforeEach(function () {
    /** @var TestCase $this */
    $this->setupBasicGame();
    // RefreshDatabase wraps each test in a transaction, which the database event store refuses to commit in
    $this->app->instance(ForCoreGameLogic::class, $this->coreGameLogic);
    // the CI does not build the frontend assets
    $this->withoutVite();

    $game = new Game();
    /** @phpstan-ignore assign.propertyType */
    $game->id = $this->getGameId()->value;
    $game->save();
});

describe('game report', function () {
    it('shows the report of a finished game with the winner and all players', function () {
        /** @var TestCase $this */
        $this->letFirstPlayerFinishLebensziel();
        [$first, $second] = $this->getPlayers();

        $this->get(route('game-play.report', ['gameId' => $this->getGameId()->value, 'playerId' => $second->value]))
            ->assertOk()
            ->assertSee('Player 0 hat das Lebensziel erreicht!')
            ->assertSee(['Player 0', 'Player 1', 'Guthaben im Spielverlauf', 'Wohin das Geld geflossen ist', 'Schlüsselmomente', 'Karrieren'])
            ->assertSee(route('game-play.index'));
    });

    it('redirects to the game while nobody has finished the Lebensziel', function () {
        /** @var TestCase $this */
        $playerId = $this->getPlayers()[0]->value;

        $this->get(route('game-play.report', ['gameId' => $this->getGameId()->value, 'playerId' => $playerId]))
            ->assertRedirect(route('game-play.game', ['gameId' => $this->getGameId()->value, 'playerId' => $playerId]));
    });

    it('rejects players that are not part of the game', function () {
        /** @var TestCase $this */
        $this->letFirstPlayerFinishLebensziel();
        $this->withExceptionHandling();

        $this->get(route('game-play.report', ['gameId' => $this->getGameId()->value, 'playerId' => 'somebody-else']))
            ->assertForbidden();
    });

    it('shows the report of a finished game that was created on an older version of the game', function () {
        /** @var TestCase $this */
        $this->letFirstPlayerFinishLebensziel();
        $this->moveGameToOlderDefinitionsVersion();
        $this->app->instance(ForCoreGameLogic::class, $this->coreGameLogic);

        $this->get(route('game-play.report', ['gameId' => $this->getGameId()->value, 'playerId' => $this->getPlayers()[0]->value]))
            ->assertOk()
            ->assertSee('Player 0 hat das Lebensziel erreicht!')
            ->assertSee('Dieses Spiel wurde mit einer älteren Version des Spiels erstellt.');
    });
});

describe('game overview', function () {
    beforeEach(function () {
        /** @var TestCase $this */
        $player = Player::create([
            'email' => 'player',
            'password' => 'password',
            // the overview reads it, a freshly created model only has the attributes it was created with
            'can_create_games' => false,
        ]);
        Game::findOrFail($this->getGameId()->value)->players()->attach($player);
        $this->actingAs($player, 'game');
        $this->player = $player;
    });

    it('marks finished games and links to their report', function () {
        /** @var TestCase $this */
        $this->letFirstPlayerFinishLebensziel();

        $this->get(route('game-play.index'))
            ->assertOk()
            ->assertSee('Beendet')
            ->assertSee('Spielbericht ansehen')
            ->assertSee(route('game-play.report', ['gameId' => $this->getGameId()->value, 'playerId' => $this->player->id]), false);
    });

    it('marks finished games of an older version of the game and links to their report', function () {
        /** @var TestCase $this */
        $this->letFirstPlayerFinishLebensziel();
        $this->moveGameToOlderDefinitionsVersion();
        $this->app->instance(ForCoreGameLogic::class, $this->coreGameLogic);

        $this->get(route('game-play.index'))
            ->assertOk()
            ->assertSee('Beendet')
            ->assertDontSee('Nicht mehr spielbar')
            ->assertSee('Spielbericht ansehen');
    });

    it('does not link to a report while the game is running', function () {
        /** @var TestCase $this */
        $this->get(route('game-play.index'))
            ->assertOk()
            ->assertSee('Gestartet')
            ->assertDontSee('Spielbericht ansehen');
    });
});
