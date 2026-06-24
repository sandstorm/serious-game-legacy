<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\GameUi;
use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\DrivingPorts\ForCoreGameLogic;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartPreGame;
use Domain\CoreGameLogic\GameId;
use Domain\CoreGameLogic\PlayerId;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    /** @var TestCase $this */
    $coreGameLogic = CoreGameLogicApp::createInMemoryForTesting();
    $this->gameId = GameId::fromString('game1');
    $this->p1 = PlayerId::fromString('p1');
    $this->p2 = PlayerId::fromString('p2');

    $coreGameLogic->handle(
        $this->gameId,
        StartPreGame::create(numberOfPlayers: 2)->withFixedPlayerIdsForTesting($this->p1, $this->p2)
    );

    $this->app->instance(ForCoreGameLogic::class, $coreGameLogic);
});

describe('selectLebensZiel', function () {
    test('selecting a valid lebensziel sets the form value', function () {
        Livewire::test(GameUi::class, ['gameId' => $this->gameId, 'myself' => $this->p1])
            ->call('selectLebensZiel', 1)
            ->assertSet('lebenszielForm.lebensziel', 1);
    });

    test('re-selecting the placeholder option resets lebensziel to null', function () {
        Livewire::test(GameUi::class, ['gameId' => $this->gameId, 'myself' => $this->p1])
            ->call('selectLebensZiel', 1)
            ->assertSet('lebenszielForm.lebensziel', 1)
            ->call('selectLebensZiel', '---')
            ->assertSet('lebenszielForm.lebensziel', null);
    });
});
