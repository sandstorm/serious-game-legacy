<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Models\Player;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);
beforeEach(function () {
    /** @var TestCase $this */
    // Laravel only skips the CSRF check automatically for APP_ENV=testing, but the container runs tests with APP_ENV=local
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

function createPlayer(string $soscisurveyId, string $password = 'correct-password'): Player
{
    /** @var Player $player */
    $player = Player::create([
        'email' => $soscisurveyId,
        'password' => $password,
        'can_create_games' => false,
    ]);
    return $player;
}

describe('player login', function () {
    it('logs in a player with valid credentials', function () {
        /** @var TestCase $this */
        createPlayer('player-a');

        $this->post('/login', ['soscisurveyId' => 'player-a', 'password' => 'correct-password'])
            ->assertRedirect('/');

        $this->assertAuthenticated('game');
    });

    it('locks a SoSciSurvey ID after 5 failed attempts, even for the correct password', function () {
        /** @var TestCase $this */
        createPlayer('player-a');

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['soscisurveyId' => 'player-a', 'password' => 'wrong-password'])
                ->assertSessionHasErrors(['soscisurveyId' => 'Anmeldung fehlgeschlagen.']);
        }

        $this->post('/login', ['soscisurveyId' => 'player-a', 'password' => 'correct-password'])
            ->assertSessionHasErrors('soscisurveyId');
        expect(session('errors')->first('soscisurveyId'))->toContain('Zu viele Anmeldeversuche');

        $this->assertGuest('game');
    });

    it('does not lock out other players from the same IP (e.g. a class behind one school network)', function () {
        /** @var TestCase $this */
        createPlayer('player-a');
        createPlayer('player-b');

        for ($i = 0; $i < 6; $i++) {
            $this->post('/login', ['soscisurveyId' => 'player-a', 'password' => 'wrong-password']);
        }

        $this->post('/login', ['soscisurveyId' => 'player-b', 'password' => 'correct-password'])
            ->assertRedirect('/');

        $this->assertAuthenticated('game');
    });

    it('resets the failed attempts of a SoSciSurvey ID after a successful login', function () {
        /** @var TestCase $this */
        createPlayer('player-a');

        for ($i = 0; $i < 4; $i++) {
            $this->post('/login', ['soscisurveyId' => 'player-a', 'password' => 'wrong-password']);
        }
        // assertRedirect('/') alone is not enough: back() also redirects to '/' if there is no previous URL
        $this->post('/login', ['soscisurveyId' => 'player-a', 'password' => 'correct-password'])
            ->assertRedirect('/');
        $this->assertAuthenticated('game');
        $this->get('/logout');

        // 4 more failed attempts would exceed the limit of 5 if the counter had not been reset
        for ($i = 0; $i < 4; $i++) {
            $this->post('/login', ['soscisurveyId' => 'player-a', 'password' => 'wrong-password'])
                ->assertSessionHasErrors(['soscisurveyId' => 'Anmeldung fehlgeschlagen.']);
        }
        $this->post('/login', ['soscisurveyId' => 'player-a', 'password' => 'correct-password'])
            ->assertRedirect('/');

        $this->assertAuthenticated('game');
    });

    it('limits failed attempts per IP across different SoSciSurvey IDs', function () {
        /** @var TestCase $this */
        createPlayer('player-a');

        for ($i = 0; $i < 100; $i++) {
            $this->post('/login', ['soscisurveyId' => "unknown-$i", 'password' => 'wrong-password']);
        }

        $this->post('/login', ['soscisurveyId' => 'player-a', 'password' => 'correct-password'])
            ->assertSessionHasErrors('soscisurveyId');
        expect(session('errors')->first('soscisurveyId'))->toContain('Zu viele Anmeldeversuche');

        $this->assertGuest('game');
    });

    it('does not reset the per IP limit after a successful login (every student has a valid account)', function () {
        /** @var TestCase $this */
        createPlayer('own-account');
        createPlayer('player-a');

        // an attacker tries other IDs and logs in with their own account in between
        for ($i = 0; $i < 99; $i++) {
            $this->post('/login', ['soscisurveyId' => "unknown-$i", 'password' => 'wrong-password']);
        }
        $this->post('/login', ['soscisurveyId' => 'own-account', 'password' => 'correct-password'])
            ->assertRedirect('/');
        $this->assertAuthenticated('game');
        $this->get('/logout');
        $this->post('/login', ['soscisurveyId' => 'unknown-99', 'password' => 'wrong-password']);

        $this->post('/login', ['soscisurveyId' => 'player-a', 'password' => 'correct-password'])
            ->assertSessionHasErrors('soscisurveyId');
        expect(session('errors')->first('soscisurveyId'))->toContain('Zu viele Anmeldeversuche');

        $this->assertGuest('game');
    });
});
