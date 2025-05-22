<?php

declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Spielzug;


use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\Dto\ValueObject\CardId;
use Domain\CoreGameLogic\Dto\ValueObject\CardRequirements;
use Domain\CoreGameLogic\Dto\ValueObject\GameId;
use Domain\CoreGameLogic\Dto\ValueObject\LebenszielId;
use Domain\CoreGameLogic\Dto\ValueObject\PileId;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\Dto\ValueObject\ResourceChanges;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Command\SelectLebensziel;
use Domain\CoreGameLogic\Feature\Initialization\Command\SetNameForPlayer;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartGame;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartPreGame;
use Domain\CoreGameLogic\Feature\Pile\Command\ShuffleCards;
use Domain\CoreGameLogic\Feature\Pile\State\dto\Pile;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasActivated;
use Domain\Definitions\Cards\Model\CardDefinition;
use Domain\Definitions\Pile\Enum\PileEnum;
use Domain\Definitions\Pile\PileFinder;

beforeEach(function () {
    $this->coreGameLogic = CoreGameLogicApp::createInMemoryForTesting();
    $this->gameId = GameId::fromString('game1');
    $this->playerId1 = PlayerId::fromString('p1');
    $this->playerId2 = PlayerId::fromString('p2');

    $this->pileIdBildung = new PileId(PileEnum::BILDUNG_PHASE_1);
    $this->cardId = new CardId('testcard');

    $this->coreGameLogic->handle($this->gameId, StartPreGame::create(
        numberOfPlayers: 2,
    )->withFixedPlayerIdsForTesting($this->playerId1, $this->playerId2));
    $this->coreGameLogic->handle($this->gameId, new SetNameForPlayer(
        playerId: $this->playerId1,
        name: 'Player 1',
    ));
    $this->coreGameLogic->handle($this->gameId, new SetNameForPlayer(
        playerId: $this->playerId2,
        name: 'Player 2',
    ));
    $this->coreGameLogic->handle($this->gameId, new SelectLebensziel(
        playerId: $this->playerId1,
        lebensziel: new LebenszielId(1),
    ));
    $this->coreGameLogic->handle($this->gameId, new SelectLebensziel(
        playerId: $this->playerId2,
        lebensziel: new LebenszielId(2),
    ));
    $this->coreGameLogic->handle(
        $this->gameId,
        new StartGame(playerOrdering: [$this->playerId1]));

    $this->coreGameLogic->handle(
        $this->gameId,
        ShuffleCards::create()->withFixedCardIdOrderForTesting(
            new Pile( pileId: $this->pileIdBildung, cards: [$this->cardId]),
        ));
});

describe('handleActivateCard', function () {

    it('Will activate a card if requirements are met', function (){
        $cardToTest = new CardDefinition(
            id: $this->cardId,
            pileId: $this->pileIdBildung,
            kurzversion: 'for testing',
            langversion: '...',
            resourceChanges: new ResourceChanges(
                guthabenChange: -200,
                bildungKompetenzsteinChange: +1,
            ),
            requirements: new CardRequirements(
                guthaben: 200,
                zeitsteine: 1
            ),
        );

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            player: $this->playerId1,
            cardId: $this->cardId,
            pile: $this->pileIdBildung,
        )->withFixedCardDefinitionForTesting($cardToTest));

        $stream = $this->coreGameLogic->getGameStream($this->gameId);
        /** @var CardWasActivated $actualEvent */
        $actualEvent = $stream->findLast(CardWasActivated::class);
        expect($actualEvent->cardId)->toEqual($this->cardId)
            ->and($actualEvent->playerId)->toEqual($this->playerId1)
            ->and($actualEvent->resourceChanges)->toEqual($cardToTest->resourceChanges);
    });

    it("will not activate the card if it's not the players turn", function () {
        $cardToTest = new CardDefinition(
            id: $this->cardId,
            pileId: $this->pileIdBildung,
            kurzversion: 'for testing',
            langversion: '...',
            resourceChanges: new ResourceChanges(
                guthabenChange: -200,
                bildungKompetenzsteinChange: +1,
            ),
            requirements: new CardRequirements(
                guthaben: 200,
                zeitsteine: 1
            ),
        );

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            player: $this->playerId2,
            cardId: $this->cardId,
            pile: $this->pileIdBildung,
        )->withFixedCardDefinitionForTesting($cardToTest));
    })->throws(\RuntimeException::class, 'Only the current player can activate a card', 1747917492);

    it("will not activate the card if the requirements are not met", function () {
        $cardToTest = new CardDefinition(
            id: $this->cardId,
            pileId: $this->pileIdBildung,
            kurzversion: 'for testing',
            langversion: '...',
            resourceChanges: new ResourceChanges(
                guthabenChange: -200,
                bildungKompetenzsteinChange: +1,
            ),
            requirements: new CardRequirements(
                guthaben: 50001,
                zeitsteine: 1
            ),
        );

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            player: $this->playerId1,
            cardId: $this->cardId,
            pile: $this->pileIdBildung,
        )->withFixedCardDefinitionForTesting($cardToTest));
    })->throws(\RuntimeException::class, 'Player p1 does not have the required resources to activate the card testcard', 1747920761);

});
