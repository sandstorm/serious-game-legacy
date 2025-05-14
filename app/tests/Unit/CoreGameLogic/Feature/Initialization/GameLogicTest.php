<?php
declare(strict_types=1);

use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\Dto\Enum\KompetenzbereichEnum;
use Domain\CoreGameLogic\Dto\ValueObject\GameId;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\Feature\Initialization\Command\LebenszielAuswaehlen;
use Domain\CoreGameLogic\Feature\Initialization\Command\SetNameForPlayer;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartGame;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartPreGame;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\KonjunkturzyklusWechseln\Command\KonjunkturzyklusWechseln;
use Domain\Definitions\Lebensziel\Model\LebenszielDefinition;
use Domain\Definitions\Lebensziel\Model\LebenszielKompetenzbereichDefinition;
use Domain\Definitions\Lebensziel\Model\LebenszielPhaseDefinition;

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
        lebensziel: new LebenszielDefinition(
            value: 'Lebensziel XYZ',
            phases: [
                new LebenszielPhaseDefinition(
                    bildungsKompetenz: new LebenszielKompetenzbereichDefinition(
                        name: KompetenzbereichEnum::BILDUNG,
                        slots: 2,
                    ),
                    freizeitKompetenz: new LebenszielKompetenzbereichDefinition(
                        name: KompetenzbereichEnum::FREIZEIT,
                        slots: 1,
                    ),
                ),
            ],
        ),
    ));
    $this->coreGameLogic->handle($this->gameId, new LebenszielAuswaehlen(
        playerId: $this->p1,
        lebensziel: new LebenszielDefinition(
            value: 'Lebensziel AAA',
            phases: [
                new LebenszielPhaseDefinition(
                    bildungsKompetenz: new LebenszielKompetenzbereichDefinition(
                        name: KompetenzbereichEnum::BILDUNG,
                        slots: 2,
                    ),
                    freizeitKompetenz: new LebenszielKompetenzbereichDefinition(
                        name: KompetenzbereichEnum::FREIZEIT,
                        slots: 1,
                    ),
                ),
            ],
        ),
    ));
});

test('Game logic - Jahr wechseln', function () {
    $this->coreGameLogic->handle($this->gameId, new StartGame([$this->p1, $this->p2]));

    // year 1
    $this->coreGameLogic->handle($this->gameId, new KonjunkturzyklusWechseln());
    $gameStream = $this->coreGameLogic->getGameStream($this->gameId);
    $year1 = GamePhaseState::currentKonjunkturzyklus($gameStream);
    expect($year1->year->value)->toEqual(1);

    // year 2
    $this->coreGameLogic->handle($this->gameId, new KonjunkturzyklusWechseln());
    $gameStream = $this->coreGameLogic->getGameStream($this->gameId);
    $year2 = GamePhaseState::currentKonjunkturzyklus($gameStream);
    expect($year2->year->value)->toEqual(2);

    // year 3
    $this->coreGameLogic->handle($this->gameId, new KonjunkturzyklusWechseln());
    $gameStream = $this->coreGameLogic->getGameStream($this->gameId);
    $year3 = GamePhaseState::currentKonjunkturzyklus($gameStream);
    expect($year3->year->value)->toEqual(3);

    // check that the years have different konjunkturzyklus
    expect($year1->konjunkturzyklus->id)->not->toEqual($year2->konjunkturzyklus->id)
        ->and($year2->konjunkturzyklus->id)->not->toEqual($year3->konjunkturzyklus->id)
        ->and($year1->konjunkturzyklus->id)->not->toEqual($year3->konjunkturzyklus->id);
});
