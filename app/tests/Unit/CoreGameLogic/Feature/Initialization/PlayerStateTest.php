<?php

declare(strict_types=1);

use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\Dto\Enum\KompetenzbereichEnum;
use Domain\CoreGameLogic\Dto\ValueObject\GameId;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\Feature\Initialization\Command\LebenszielAuswaehlen;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartPreGame;
use Domain\CoreGameLogic\Feature\Initialization\State\LebenszielAccessor;
use Domain\Definitions\Lebensziel\Model\Lebensziel;
use Domain\Definitions\Lebensziel\Model\LebenszielKompetenzbereich;
use Domain\Definitions\Lebensziel\Model\LebenszielPhase;

beforeEach(function () {
    $this->coreGameLogic = CoreGameLogicApp::createInMemoryForTesting();
    $this->gameId = GameId::fromString('game1');
    $this->p1 = PlayerId::fromString('p1');
    $this->p2 = PlayerId::fromString('p2');
});


test('kompetenzstein state', function () {
    $this->coreGameLogic->handle($this->gameId, StartPreGame::create(
        numberOfPlayers: 2,
    )->withFixedPlayerIdsForTesting($this->p1, $this->p2));
    $this->coreGameLogic->handle($this->gameId, new LebenszielAuswaehlen(
        playerId: $this->p1,
        lebensziel: new Lebensziel(
            'Influencer',
            phases: [
                new LebenszielPhase(
                    kompetenzen: [
                        KompetenzbereichEnum::BILDUNG->name => new LebenszielKompetenzbereich(
                            name: KompetenzbereichEnum::BILDUNG,
                            slots: 2,
                        ),
                        KompetenzbereichEnum::FREIZEIT->name => new LebenszielKompetenzbereich(
                            name: KompetenzbereichEnum::FREIZEIT,
                            slots: 1,
                        ),
                    ]
                )
            ]
        ),
    ));
    $this->coreGameLogic->handle($this->gameId, new LebenszielAuswaehlen(
        playerId: $this->p2,
        lebensziel: new Lebensziel(
            'Selbstversorger Kanada',
            phases: [
                new LebenszielPhase(
                    kompetenzen: [
                        KompetenzbereichEnum::BILDUNG->name => new LebenszielKompetenzbereich(
                            name: KompetenzbereichEnum::BILDUNG,
                            slots: 1,
                        ),
                        KompetenzbereichEnum::FREIZEIT->name => new LebenszielKompetenzbereich(
                            name: KompetenzbereichEnum::FREIZEIT,
                            slots: 3,
                        ),
                    ]
                )
            ]
        ),
    ));

    $gameStream = $this->coreGameLogic->getGameStream($this->gameId);
    // player 1
    var_dump(LebenszielAccessor::forStream($gameStream)->forPlayer($this->p1));
    // bildung
    expect(LebenszielAccessor::forStream($gameStream)->forPlayer($this->p1)->phases[0]->kompetenzen[KompetenzbereichEnum::BILDUNG->name]->slots)->toBe(2);
    expect(LebenszielAccessor::forStream($gameStream)->forPlayer($this->p1)->phases[0]->kompetenzen[KompetenzbereichEnum::BILDUNG->name]->placed)->toBe(0);
    // freizeit
    expect(LebenszielAccessor::forStream($gameStream)->forPlayer($this->p1)->phases[0]->kompetenzen[KompetenzbereichEnum::FREIZEIT->name]->slots)->toBe(1);
    expect(LebenszielAccessor::forStream($gameStream)->forPlayer($this->p1)->phases[0]->kompetenzen[KompetenzbereichEnum::FREIZEIT->name]->placed)->toBe(0);

    //player 2
    // bildung
    expect(LebenszielAccessor::forStream($gameStream)->forPlayer($this->p2)->phases[0]->kompetenzen[KompetenzbereichEnum::BILDUNG->name]->slots)->toBe(1);
    expect(LebenszielAccessor::forStream($gameStream)->forPlayer($this->p2)->phases[0]->kompetenzen[KompetenzbereichEnum::BILDUNG->name]->placed)->toBe(0);
    //freizeit
    expect(LebenszielAccessor::forStream($gameStream)->forPlayer($this->p2)->phases[0]->kompetenzen[KompetenzbereichEnum::FREIZEIT->name]->slots)->toBe(3);
    expect(LebenszielAccessor::forStream($gameStream)->forPlayer($this->p2)->phases[0]->kompetenzen[KompetenzbereichEnum::FREIZEIT->name]->placed)->toBe(0);
});
