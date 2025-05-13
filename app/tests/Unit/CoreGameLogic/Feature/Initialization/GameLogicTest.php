<?php
declare(strict_types=1);

use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\Dto\ValueObject\GameId;
use Domain\CoreGameLogic\Dto\ValueObject\Lebensziel;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\Dto\ValueObject\Szenario;
use Domain\CoreGameLogic\Feature\Initialization\Command\JahrWechseln;
use Domain\CoreGameLogic\Feature\Initialization\Command\LebenszielAuswaehlen;
use Domain\CoreGameLogic\Feature\Initialization\Command\SetNameForPlayer;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartGame;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartPreGame;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;

beforeEach(function () {
    $this->coreGameLogic = CoreGameLogicApp::createInMemoryForTesting();
    $this->gameId = GameId::fromString('game1');
    $this->p1 = PlayerId::fromString('p1');
    $this->p2 = PlayerId::fromString('p2');

    $this->coreGameLogic->handle($this->gameId, StartPreGame::create(
        numberOfPlayers: 2,
    )->withFixedPlayerIdsForTesting($this->p1, $this->p2));
    $this->coreGameLogic->handle($this->gameId, new SetNameForPlayer(
        playerId: $this->p1,
        name: 'Player 1a',
    ));
    $this->coreGameLogic->handle($this->gameId, new SetNameForPlayer(
        playerId: $this->p2,
        name: 'Player 2',
    ));
    $this->coreGameLogic->handle($this->gameId, new LebenszielAuswaehlen(
        playerId: $this->p2,
        lebensziel: new Lebensziel('Lebensziel XYZ'),
    ));
    $this->coreGameLogic->handle($this->gameId, new LebenszielAuswaehlen(
        playerId: $this->p1,
        lebensziel: new Lebensziel('Lebensziel AAA'),
    ));
});

test('Game logic - Jahr wechseln', function () {
    $this->coreGameLogic->handle($this->gameId, new StartGame([$this->p1, $this->p2]));
    $this->coreGameLogic->handle($this->gameId, new JahrWechseln(
        name: 'Jahr 1',
        szenario: new Szenario('Szenario XYZ', 'Beschreibung', []),
    ));

    $gameStream = $this->coreGameLogic->getGameStream($this->gameId);
    expect(GamePhaseState::currentYear($gameStream)->name)->toEqual('Jahr 1');
    expect(GamePhaseState::currentYear($gameStream)->szenario->value)->toEqual('Szenario XYZ');
    expect(count(GamePhaseState::currentYear($gameStream)->szenario->categories))->toEqual(4);
    expect(GamePhaseState::currentYear($gameStream)->szenario->categories[0]->name)->toEqual('Bildung & Karriere');
    expect(GamePhaseState::currentYear($gameStream)->szenario->categories[0]->zeitSlots)->toEqual(2);

    $this->coreGameLogic->handle($this->gameId, new JahrWechseln(
        name: 'Jahr 2',
        szenario: new Szenario('Szenario ABC', 'Beschreibung', []),
    ));

    $gameStream = $this->coreGameLogic->getGameStream($this->gameId);
    expect(GamePhaseState::currentYear($gameStream)->name)->toEqual('Jahr 2');
    expect(GamePhaseState::currentYear($gameStream)->szenario->value)->toEqual('Szenario ABC');
});
