<?php

declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Spielzug;


use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\Dto\ValueObject\GameId;
use Domain\CoreGameLogic\Dto\ValueObject\LebenszielId;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\Feature\Initialization\Command\SelectLebensziel;
use Domain\CoreGameLogic\Feature\Initialization\Command\SetNameForPlayer;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartGame;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartPreGame;
use Domain\CoreGameLogic\Feature\Pile\Command\ShuffleCards;
use Domain\CoreGameLogic\Feature\Pile\State\dto\Pile;
use Domain\CoreGameLogic\Feature\Player\State\PlayerState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SkipCard;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasActivated;
use Domain\Definitions\Card\Dto\CardDefinition;
use Domain\Definitions\Card\Dto\CardRequirements;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\PileEnum;
use Domain\Definitions\Card\ValueObject\PileId;

beforeEach(function () {
    $this->coreGameLogic = CoreGameLogicApp::createInMemoryForTesting();
    $this->gameId = GameId::fromString('game1');
    $this->playerId1 = PlayerId::fromString('p1');
    $this->playerId2 = PlayerId::fromString('p2');

    $this->pileIdBildung = new PileId(PileEnum::BILDUNG_PHASE_1);

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
        StartGame::create());
});


describe('handleSkipCard', function () {

    it('will consume a Zeitstein', function () {
        $skipThisCard = new CardId('skipped');

        $this->coreGameLogic->handle(
            $this->gameId,
            ShuffleCards::create()->withFixedCardIdOrderForTesting(
                new Pile( pileId: $this->pileIdBildung, cards: [$skipThisCard]),
            ));

        $this->coreGameLogic->handle($this->gameId, new SkipCard(
            player: $this->playerId1,
            card: $skipThisCard,
            pile: $this->pileIdBildung,
        ));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->playerId1))->toBe(2);
    });
});

describe('handleActivateCard', function () {

    it('will consume a Zeitstein (first turn)', function (){
        $cardToTest = new CardDefinition(
            id: new CardId('testcard'),
            pileId: $this->pileIdBildung,
            kurzversion: 'for testing',
            langversion: '...',
            resourceChanges: new ResourceChanges(
                guthabenChange: -200,
                bildungKompetenzsteinChange: +1,
            ),
            additionalRequirements: new CardRequirements(
                guthaben: 200,
            ),
        );

        $this->coreGameLogic->handle(
            $this->gameId,
            ShuffleCards::create()->withFixedCardIdOrderForTesting(
                new Pile( pileId: $this->pileIdBildung, cards: [$cardToTest->id]),
            ));

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            player: $this->playerId1,
            cardId: $cardToTest->id,
            pile: $this->pileIdBildung,
        )->withFixedCardDefinitionForTesting($cardToTest));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->playerId1))->toBe(2);
    });

    it('will consume a Zeitstein (later turns)', function (){
        $skipThisCard = new CardId('skipped');
        $cardToTest = new CardDefinition(
            id: new CardId('testcard'),
            pileId: $this->pileIdBildung,
            kurzversion: 'for testing',
            langversion: '...',
            resourceChanges: new ResourceChanges(
                guthabenChange: -200,
                bildungKompetenzsteinChange: +1,
            ),
            additionalRequirements: new CardRequirements(
                guthaben: 200,
            ),
        );

        $this->coreGameLogic->handle(
            $this->gameId,
            ShuffleCards::create()->withFixedCardIdOrderForTesting(
                new Pile( pileId: $this->pileIdBildung, cards: [$skipThisCard, $cardToTest->id]),
            ));

        $this->coreGameLogic->handle($this->gameId, new SkipCard(
            player: $this->playerId1,
            card: $skipThisCard,
            pile: $this->pileIdBildung,
        ));

        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->playerId1)
        );

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            player: $this->playerId2,
            cardId: $cardToTest->id,
            pile: $this->pileIdBildung,
        )->withFixedCardDefinitionForTesting($cardToTest));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->playerId2))->toBe(2);
    });

    it('will not consume a Zeitstein after skipping a Card', function (){
        $skipThisCard = new CardId('skipped');
        $cardToTest = new CardDefinition(
            id: new CardId('testcard'),
            pileId: $this->pileIdBildung,
            kurzversion: 'for testing',
            langversion: '...',
            resourceChanges: new ResourceChanges(
                guthabenChange: -200,
                bildungKompetenzsteinChange: +1,
            ),
            additionalRequirements: new CardRequirements(
                guthaben: 200,
            ),
        );

        $this->coreGameLogic->handle(
            $this->gameId,
            ShuffleCards::create()->withFixedCardIdOrderForTesting(
                new Pile( pileId: $this->pileIdBildung, cards: [$skipThisCard, $cardToTest->id]),
            ));

        $this->coreGameLogic->handle($this->gameId, new SkipCard(
            player: $this->playerId1,
            card: $skipThisCard,
            pile: $this->pileIdBildung,
        ));

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            player: $this->playerId1,
            cardId: $cardToTest->id,
            pile: $this->pileIdBildung,
        )->withFixedCardDefinitionForTesting($cardToTest));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->playerId1))->toBe(2);
    });



    it('Will activate a card if requirements are met', function (){
        $cardToTest = new CardDefinition(
            id: new CardId('testcard'),
            pileId: $this->pileIdBildung,
            kurzversion: 'for testing',
            langversion: '...',
            resourceChanges: new ResourceChanges(
                guthabenChange: -200,
                bildungKompetenzsteinChange: +1,
            ),
            additionalRequirements: new CardRequirements(
                guthaben: 200,
            ),
        );

        $this->coreGameLogic->handle(
            $this->gameId,
            ShuffleCards::create()->withFixedCardIdOrderForTesting(
                new Pile( pileId: $this->pileIdBildung, cards: [$cardToTest->id]),
            ));

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            player: $this->playerId1,
            cardId: $cardToTest->id,
            pile: $this->pileIdBildung,
        )->withFixedCardDefinitionForTesting($cardToTest));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        /** @var CardWasActivated $actualEvent */
        $actualEvent = $stream->findLast(CardWasActivated::class);
        // expect to lose an additional Zeitstein for activating the card
        $expectedResourceChanges = $cardToTest->resourceChanges->accumulate(new ResourceChanges(zeitsteineChange: -1));
        expect($actualEvent->cardId)->toEqual($cardToTest->id)
            ->and($actualEvent->playerId)->toEqual($this->playerId1)
            ->and($actualEvent->resourceChanges)->toEqual($expectedResourceChanges);
    });

    it("will not activate the card if it's not the players turn", function () {
        $cardToTest = new CardDefinition(
            id: new CardId('testcard'),
            pileId: $this->pileIdBildung,
            kurzversion: 'for testing',
            langversion: '...',
            resourceChanges: new ResourceChanges(
                guthabenChange: -200,
                bildungKompetenzsteinChange: +1,
            ),
            additionalRequirements: new CardRequirements(
                guthaben: 200,
                zeitsteine: 1
            ),
        );

        $this->coreGameLogic->handle(
            $this->gameId,
            ShuffleCards::create()->withFixedCardIdOrderForTesting(
                new Pile( pileId: $this->pileIdBildung, cards: [$cardToTest->id]),
            ));

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            player: $this->playerId2,
            cardId: $cardToTest->id,
            pile: $this->pileIdBildung,
        )->withFixedCardDefinitionForTesting($cardToTest));
    })->throws(\RuntimeException::class, 'Only the current player can activate a card', 1747917492);

    it("will not activate the card if the requirements are not met", function () {
        $cardToTest = new CardDefinition(
            id: new CardId('testcard'),
            pileId: $this->pileIdBildung,
            kurzversion: 'for testing',
            langversion: '...',
            resourceChanges: new ResourceChanges(
                guthabenChange: -50001,
                bildungKompetenzsteinChange: +1,
            ),
            additionalRequirements: new CardRequirements(),
        );

        $this->coreGameLogic->handle(
            $this->gameId,
            ShuffleCards::create()->withFixedCardIdOrderForTesting(
                new Pile( pileId: $this->pileIdBildung, cards: [$cardToTest->id]),
            ));

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            player: $this->playerId1,
            cardId: $cardToTest->id,
            pile: $this->pileIdBildung,
        )->withFixedCardDefinitionForTesting($cardToTest));
    })->throws(\RuntimeException::class, 'Player p1 does not have the required resources ([guthabenChange: 50000 zeitsteineChange: 3] to activate the card testcard ([guthabenChange: -50001 zeitsteineChange: -1])', 1747920761);

});
