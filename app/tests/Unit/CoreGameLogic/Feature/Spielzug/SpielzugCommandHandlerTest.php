<?php

declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Spielzug;


use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\CardOrder;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\RequestJobOffers;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SkipCard;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasActivated;
use Domain\CoreGameLogic\Feature\Spielzug\Event\JobOffersWereRequested;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;

beforeEach(function () {
        $this->setupBasicGame();
});


describe('handleSkipCard', function () {

    it('will consume a Zeitstein', function () {
        $skipThisCard = new CardId('skipped');

        $this->coreGameLogic->handle(
            $this->gameId,
            ChangeKonjunkturphase::create()->withFixedCardOrderForTesting(
                new CardOrder( pileId: $this->pileIdBildung, cards: [$skipThisCard]),
            ));

        $this->coreGameLogic->handle($this->gameId, new SkipCard(
            player: $this->player1,
            card: $skipThisCard,
            pile: $this->pileIdBildung,
        ));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->player1))->toBe(2);
    });
});

describe('handleActivateCard', function () {

    it('will consume a Zeitstein (first turn)', function (){
        $cardToTest = new KategorieCardDefinition(
            id: new CardId('testcard'),
            pileId: $this->pileIdBildung,
            title: 'for testing',
            description: '...',
            resourceChanges: new ResourceChanges(
                guthabenChange: -200,
                bildungKompetenzsteinChange: +1,
            ),
        );

        $this->coreGameLogic->handle(
            $this->gameId,
            ChangeKonjunkturphase::create()->withFixedCardOrderForTesting(
                new CardOrder( pileId: $this->pileIdBildung, cards: [$cardToTest->id]),
            ));

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            player: $this->player1,
            cardId: $cardToTest->id,
            pile: $this->pileIdBildung,
        )->withFixedCardDefinitionForTesting($cardToTest));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->player1))->toBe(2);
    });

    it('will consume a Zeitstein (later turns)', function (){
        $skipThisCard = new CardId('skipped');
        $cardToTest = new KategorieCardDefinition(
            id: new CardId('testcard'),
            pileId: $this->pileIdBildung,
            title: 'for testing',
            description: '...',
            resourceChanges: new ResourceChanges(
                guthabenChange: -200,
                bildungKompetenzsteinChange: +1,
            ),
        );

        $this->coreGameLogic->handle(
            $this->gameId,
            ChangeKonjunkturphase::create()->withFixedCardOrderForTesting(
                new CardOrder( pileId: $this->pileIdBildung, cards: [$skipThisCard, $cardToTest->id]),
            ));

        $this->coreGameLogic->handle($this->gameId, new SkipCard(
            player: $this->player1,
            card: $skipThisCard,
            pile: $this->pileIdBildung,
        ));

        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->player1)
        );

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            player: $this->player2,
            cardId: $cardToTest->id,
            pile: $this->pileIdBildung,
        )->withFixedCardDefinitionForTesting($cardToTest));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->player2))->toBe(2);
    });

    it('will not consume a Zeitstein after skipping a Card', function (){
        $skipThisCard = new CardId('skipped');
        $cardToTest = new KategorieCardDefinition(
            id: new CardId('testcard'),
            pileId: $this->pileIdBildung,
            title: 'for testing',
            description: '...',
            resourceChanges: new ResourceChanges(
                guthabenChange: -200,
                bildungKompetenzsteinChange: +1,
            ),
        );

        $this->coreGameLogic->handle(
            $this->gameId,
            ChangeKonjunkturphase::create()->withFixedCardOrderForTesting(
                new CardOrder( pileId: $this->pileIdBildung, cards: [$skipThisCard, $cardToTest->id]),
            ));

        $this->coreGameLogic->handle($this->gameId, new SkipCard(
            player: $this->player1,
            card: $skipThisCard,
            pile: $this->pileIdBildung,
        ));

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            player: $this->player1,
            cardId: $cardToTest->id,
            pile: $this->pileIdBildung,
        )->withFixedCardDefinitionForTesting($cardToTest));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->player1))->toBe(2);
    });



    it('Will activate a card if requirements are met', function (){
        $cardToTest = new KategorieCardDefinition(
            id: new CardId('testcard'),
            pileId: $this->pileIdBildung,
            title: 'for testing',
            description: '...',
            resourceChanges: new ResourceChanges(
                guthabenChange: -200,
                bildungKompetenzsteinChange: +1,
            ),
        );

        $this->coreGameLogic->handle(
            $this->gameId,
            ChangeKonjunkturphase::create()->withFixedCardOrderForTesting(
                new CardOrder( pileId: $this->pileIdBildung, cards: [$cardToTest->id]),
            ));

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            player: $this->player1,
            cardId: $cardToTest->id,
            pile: $this->pileIdBildung,
        )->withFixedCardDefinitionForTesting($cardToTest));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        /** @var CardWasActivated $actualEvent */
        $actualEvent = $stream->findLast(CardWasActivated::class);
        // expect to lose an additional Zeitstein for activating the card
        $expectedResourceChanges = $cardToTest->resourceChanges->accumulate(new ResourceChanges(zeitsteineChange: -1));
        expect($actualEvent->cardId)->toEqual($cardToTest->id)
            ->and($actualEvent->playerId)->toEqual($this->player1)
            ->and($actualEvent->resourceChanges)->toEqual($expectedResourceChanges);
    });

    it("will not activate the card if it's not the players turn", function () {
        $cardToTest = new KategorieCardDefinition(
            id: new CardId('testcard'),
            pileId: $this->pileIdBildung,
            title: 'for testing',
            description: '...',
            resourceChanges: new ResourceChanges(
                guthabenChange: -200,
                bildungKompetenzsteinChange: +1,
            ),
        );

        $this->coreGameLogic->handle(
            $this->gameId,
            ChangeKonjunkturphase::create()->withFixedCardOrderForTesting(
                new CardOrder( pileId: $this->pileIdBildung, cards: [$cardToTest->id]),
            ));

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            player: $this->player2,
            cardId: $cardToTest->id,
            pile: $this->pileIdBildung,
        )->withFixedCardDefinitionForTesting($cardToTest));
    })->throws(\RuntimeException::class, 'Only the current player can activate a card', 1747917492);

    it("will not activate the card if the requirements are not met", function () {
        $cardToTest = new KategorieCardDefinition(
            id: new CardId('testcard'),
            pileId: $this->pileIdBildung,
            title: 'for testing',
            description: '...',
            resourceChanges: new ResourceChanges(
                guthabenChange: -50001,
                bildungKompetenzsteinChange: +1,
            ),
        );

        $this->coreGameLogic->handle(
            $this->gameId,
            ChangeKonjunkturphase::create()->withFixedCardOrderForTesting(
                new CardOrder( pileId: $this->pileIdBildung, cards: [$cardToTest->id]),
            ));

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            player: $this->player1,
            cardId: $cardToTest->id,
            pile: $this->pileIdBildung,
        )->withFixedCardDefinitionForTesting($cardToTest));
    })->throws(\RuntimeException::class, 'Player p1 does not have the required resources ([guthabenChange: 50000 zeitsteineChange: 3] to activate the card testcard ([guthabenChange: -50001 zeitsteineChange: -1])', 1747920761);

});

describe('handleRequestJobOffers', function () {
    it('returns no jobs when the player does not match any requirements', function (){
        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->player1));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var JobOffersWereRequested $actualEvent */
        $actualEvent = $stream->findLast(JobOffersWereRequested::class);
        expect($actualEvent->player)->toEqual($this->player1)
            ->and($actualEvent->jobs)->toEqual([]);
    });

    it('returns 3 jobs with fulfilled requirements', function (){
        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->player1));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var JobOffersWereRequested $actualEvent */
        $actualEvent = $stream->findLast(JobOffersWereRequested::class);
        expect($actualEvent->player)->toEqual($this->player1);
//            ->and($actualEvent->jobs[0]->id)->toEqual($this->player1);
    });
});
