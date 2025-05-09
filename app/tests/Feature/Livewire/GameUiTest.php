<?php

use App\Livewire\GameUi;
use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\Dto\ValueObject\GameId;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);
/*
it('renders successfully', function () {
    $gameId = new GameId('g1');
    $coreGameLogic = CoreGameLogicApp::createInMemoryForTesting();
    $coreGameLogic->startGameIfNotStarted($gameId);

    Livewire::test(GameUi::class, [
        'gameId' => $gameId
    ])
        ->assertStatus(200);
});
*/
