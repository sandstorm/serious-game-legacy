<?php

declare(strict_types=1);

use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\Feature\Initialization\Command\DefinePlayerOrdering;
use Domain\CoreGameLogic\Feature\Initialization\Command\SelectLebensziel;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartPreGame;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\CardOrder;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\GameId;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\PileFinder;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Lebensziel\ValueObject\LebenszielId;

beforeEach(function () {
    $this->coreGameLogic = CoreGameLogicApp::createInMemoryForTesting();
    $this->gameId = GameId::fromString('game1');
    $this->p1 = PlayerId::fromString('p1');
    $this->p2 = PlayerId::fromString('p2');
    $this->pileIdBildung = PileId::BILDUNG_PHASE_1;
    $this->cardsBildung = PileFinder::getCardsIdsForPile($this->pileIdBildung);
    $this->coreGameLogic->handle($this->gameId, StartPreGame::create(
        numberOfPlayers: 2,
    )->withFixedPlayerIdsForTesting($this->p1, $this->p2));
    $this->coreGameLogic->handle($this->gameId, new SelectLebensziel(
        playerId: $this->p1,
        lebensziel: LebenszielId::create(1),
    ));
    $this->coreGameLogic->handle($this->gameId, new SelectLebensziel(
        playerId: $this->p2,
        lebensziel: LebenszielId::create(2),
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
    $cardToTest = new KategorieCardDefinition(
        id: $cardId,
        pileId: PileId::BILDUNG_PHASE_1,
        title: 'Sprachkurs',
        description: 'Mache einen Sprachkurs über drei Monate im Ausland.',
        resourceChanges: new ResourceChanges(
            guthabenChange: -11000,
            bildungKompetenzsteinChange: +1,
        ),
    );
    CardFinder::getInstance()->overrideCardsForTesting([
        PileId::BILDUNG_PHASE_1->value => [
            "test1" => $cardToTest,
        ],
        PileId::FREIZEIT_PHASE_1->value => [],
        PileId::JOBS_PHASE_1->value => [],
    ]);

    $this->coreGameLogic->handle(
        $this->gameId,
        ChangeKonjunkturphase::create()->withFixedCardOrderForTesting(
            new CardOrder( pileId: $this->pileIdBildung, cards: [$cardId]),
        ));

    $gameStream = $this->coreGameLogic->getGameEvents($this->gameId);
    // player 1
    // bildung
    expect(PreGameState::lebenszielForPlayer($gameStream, $this->p1)->phases[0]->definition->bildungsKompetenzSlots)->toBe(2);
    expect(PreGameState::lebenszielForPlayer($gameStream, $this->p1)->phases[0]->placedKompetenzsteineBildung)->toBe(0);
    // freizeit
    expect(PreGameState::lebenszielForPlayer($gameStream, $this->p1)->phases[0]->definition->freizeitKompetenzSlots)->toBe(1);
    expect(PreGameState::lebenszielForPlayer($gameStream, $this->p1)->phases[0]->placedKompetenzsteineFreizeit)->toBe(0);

    //player 2
    // bildung
    expect(PreGameState::lebenszielForPlayer($gameStream, $this->p2)->phases[0]->definition->bildungsKompetenzSlots)->toBe(2);
    expect(PreGameState::lebenszielForPlayer($gameStream, $this->p2)->phases[0]->placedKompetenzsteineBildung)->toBe(0);
    //freizeit
    expect(PreGameState::lebenszielForPlayer($gameStream, $this->p2)->phases[0]->definition->freizeitKompetenzSlots)->toBe(1);
    expect(PreGameState::lebenszielForPlayer($gameStream, $this->p2)->phases[0]->placedKompetenzsteineFreizeit)->toBe(0);

    $this->coreGameLogic->handle(
        $this->gameId,
        ActivateCard::create($this->p1, $cardToTest->id, $this->pileIdBildung));
    $gameStream = $this->coreGameLogic->getGameEvents($this->gameId);

    // player 1
    // bildung
    expect(PreGameState::lebenszielForPlayer($gameStream, $this->p1)->phases[0]->definition->bildungsKompetenzSlots)->toBe(2);
    expect(PreGameState::lebenszielForPlayer($gameStream, $this->p1)->phases[0]->placedKompetenzsteineBildung)->toBe(1);
    // freizeit
    expect(PreGameState::lebenszielForPlayer($gameStream, $this->p1)->phases[0]->definition->freizeitKompetenzSlots)->toBe(1);
    expect(PreGameState::lebenszielForPlayer($gameStream, $this->p1)->phases[0]->placedKompetenzsteineFreizeit)->toBe(0);

    //player 2 unchanged
    // bildung
    expect(PreGameState::lebenszielForPlayer($gameStream, $this->p2)->phases[0]->definition->bildungsKompetenzSlots)->toBe(2);
    expect(PreGameState::lebenszielForPlayer($gameStream, $this->p2)->phases[0]->placedKompetenzsteineBildung)->toBe(0);
    //freizeit
    expect(PreGameState::lebenszielForPlayer($gameStream, $this->p2)->phases[0]->definition->freizeitKompetenzSlots)->toBe(1);
    expect(PreGameState::lebenszielForPlayer($gameStream, $this->p2)->phases[0]->placedKompetenzsteineFreizeit)->toBe(0);
});
