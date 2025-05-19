<?php

declare(strict_types=1);

use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\Dto\ValueObject\CardId;
use Domain\CoreGameLogic\Dto\ValueObject\GameId;
use Domain\CoreGameLogic\Dto\ValueObject\LebenszielId;
use Domain\CoreGameLogic\Dto\ValueObject\PileId;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\Dto\ValueObject\CardRequirements;
use Domain\CoreGameLogic\Dto\ValueObject\ResourceChanges;
use Domain\CoreGameLogic\Feature\Initialization\Command\DefinePlayerOrdering;
use Domain\CoreGameLogic\Feature\Initialization\Command\SelectLebensziel;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartPreGame;
use Domain\CoreGameLogic\Feature\Initialization\State\LebenszielAccessor;
use Domain\CoreGameLogic\Feature\Pile\Command\ShuffleCards;
use Domain\CoreGameLogic\Feature\Pile\State\dto\Pile;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\Definitions\Cards\Model\CardDefinition;
use Domain\Definitions\Pile\Enum\PileEnum;
use Domain\Definitions\Pile\PileFinder;

beforeEach(function () {
    $this->coreGameLogic = CoreGameLogicApp::createInMemoryForTesting();
    $this->gameId = GameId::fromString('game1');
    $this->p1 = PlayerId::fromString('p1');
    $this->p2 = PlayerId::fromString('p2');
    $this->pileIdBildung = new PileId(PileEnum::BILDUNG_PHASE_1);
    $this->cardsBildung = PileFinder::getCardsIdsForPile($this->pileIdBildung);
    $this->coreGameLogic->handle($this->gameId, StartPreGame::create(
        numberOfPlayers: 2,
    )->withFixedPlayerIdsForTesting($this->p1, $this->p2));
    $this->coreGameLogic->handle($this->gameId, new SelectLebensziel(
        playerId: $this->p1,
        lebensziel: new LebenszielId(1),
    ));
    $this->coreGameLogic->handle($this->gameId, new SelectLebensziel(
        playerId: $this->p2,
        lebensziel: new LebenszielId(2),
    ));

    $this->coreGameLogic->handle($this->gameId, new DefinePlayerOrdering(
        playerOrdering: [
            $this->p1,
            $this->p2,
        ]
    ));
});


test('kompetenzstein state', function () {
    $cardId = new CardId('test1');

    $this->coreGameLogic->handle(
        $this->gameId,
        ShuffleCards::create()->withFixedCardIdOrderForTesting(
            new Pile( pileId: $this->pileIdBildung, cards: [$cardId]),
        ));

    $gameStream = $this->coreGameLogic->getGameStream($this->gameId);
    // player 1
    // bildung
    expect(LebenszielAccessor::forStream($gameStream)->forPlayer($this->p1)->phases[0]->definition->bildungsKompetenzSlots)->toBe(2);
    expect(LebenszielAccessor::forStream($gameStream)->forPlayer($this->p1)->phases[0]->placedKompetenzsteineBildung)->toBe(0);
    // freizeit
    expect(LebenszielAccessor::forStream($gameStream)->forPlayer($this->p1)->phases[0]->definition->freizeitKompetenzSlots)->toBe(1);
    expect(LebenszielAccessor::forStream($gameStream)->forPlayer($this->p1)->phases[0]->placedKompetenzsteineFreizeit)->toBe(0);

    //player 2
    // bildung
    expect(LebenszielAccessor::forStream($gameStream)->forPlayer($this->p2)->phases[0]->definition->bildungsKompetenzSlots)->toBe(2);
    expect(LebenszielAccessor::forStream($gameStream)->forPlayer($this->p2)->phases[0]->placedKompetenzsteineBildung)->toBe(0);
    //freizeit
    expect(LebenszielAccessor::forStream($gameStream)->forPlayer($this->p2)->phases[0]->definition->freizeitKompetenzSlots)->toBe(1);
    expect(LebenszielAccessor::forStream($gameStream)->forPlayer($this->p2)->phases[0]->placedKompetenzsteineFreizeit)->toBe(0);

    $this->coreGameLogic->handle(
        $this->gameId,
        ActivateCard::create($this->p1, array_shift($this->cardsBildung), $this->pileIdBildung)
            ->withFixedCardDefinitionForTesting(
                new CardDefinition(
                    id: $cardId,
                    pileId: new PileId(PileEnum::BILDUNG_PHASE_1),
                    kurzversion: 'Sprachkurs',
                    langversion: 'Mache einen Sprachkurs über drei Monate im Ausland.',
                    resourceChanges: new ResourceChanges(
                        guthabenChange: -11000,
                        bildungKompetenzsteinChange: +1,
                    ),
                    requirements: new CardRequirements(
                        guthaben: 11000,
                    ),
                )
            ));
    $gameStream = $this->coreGameLogic->getGameStream($this->gameId);

    // player 1
    // bildung
    expect(LebenszielAccessor::forStream($gameStream)->forPlayer($this->p1)->phases[0]->definition->bildungsKompetenzSlots)->toBe(2);
    expect(LebenszielAccessor::forStream($gameStream)->forPlayer($this->p1)->phases[0]->placedKompetenzsteineBildung)->toBe(1);
    // freizeit
    expect(LebenszielAccessor::forStream($gameStream)->forPlayer($this->p1)->phases[0]->definition->freizeitKompetenzSlots)->toBe(1);
    expect(LebenszielAccessor::forStream($gameStream)->forPlayer($this->p1)->phases[0]->placedKompetenzsteineFreizeit)->toBe(0);

    //player 2 unchanged
    // bildung
    expect(LebenszielAccessor::forStream($gameStream)->forPlayer($this->p2)->phases[0]->definition->bildungsKompetenzSlots)->toBe(2);
    expect(LebenszielAccessor::forStream($gameStream)->forPlayer($this->p2)->phases[0]->placedKompetenzsteineBildung)->toBe(0);
    //freizeit
    expect(LebenszielAccessor::forStream($gameStream)->forPlayer($this->p2)->phases[0]->definition->freizeitKompetenzSlots)->toBe(1);
    expect(LebenszielAccessor::forStream($gameStream)->forPlayer($this->p2)->phases[0]->placedKompetenzsteineFreizeit)->toBe(0);
});
