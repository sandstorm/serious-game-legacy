<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Models\Game;
use App\Models\Player;
use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\DrivingPorts\ForCoreGameLogic;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);
beforeEach(function () {
    /** @var TestCase $this */
    // Laravel only skips the CSRF check automatically for APP_ENV=testing, but the container runs tests with APP_ENV=local
    $this->withoutMiddleware(ValidateCsrfToken::class);
    // RefreshDatabase wraps each test in a transaction, which the database event store refuses to commit in
    $this->app->instance(ForCoreGameLogic::class, CoreGameLogicApp::createInMemoryForTesting());

    /** @var Player $player */
    $player = Player::create([
        'email' => 'game-creator',
        'password' => 'password',
        'can_create_games' => true,
    ]);
    $this->actingAs($player, 'game');
});

describe('create game', function () {
    it('offers the allowed numbers of players in the form', function () {
        /** @var TestCase $this */
        // the CI does not build the frontend assets
        $this->withoutVite();
        $this->get('/new-game')
            ->assertOk()
            ->assertSeeInOrder(['2 Spieler:innen', '3 Spieler:innen', '4 Spieler:innen'])
            ->assertDontSee('5 Spieler:innen')
            ->assertSee('min="2" max="4"', false);
    });

    it('creates a game with the maximum number of players', function () {
        /** @var TestCase $this */
        $this->post('/new-game', ['numberOfPlayers' => 4])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        expect(Game::count())->toBe(1);
    });

    it('rejects more players than allowed with a validation error', function () {
        /** @var TestCase $this */
        // the form only offers 2-4 players, but a manipulated request could send any number
        // exception handling is needed to turn the ValidationException into a redirect with errors (see Pest.php)
        $this->withExceptionHandling();
        $this->post('/new-game', ['numberOfPlayers' => 5])
            ->assertSessionHasErrors('numberOfPlayers');

        expect(Game::count())->toBe(0);
    });
});
