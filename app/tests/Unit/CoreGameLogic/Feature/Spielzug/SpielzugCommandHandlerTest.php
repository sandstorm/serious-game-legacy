<?php

declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Spielzug;


use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\CardOrder;
use Domain\CoreGameLogic\Feature\Spielzug\Command\AcceptJobOffer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\RequestJobOffers;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SkipCard;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasActivated;
use Domain\CoreGameLogic\Feature\Spielzug\Event\JobOffersWereRequested;
use Domain\CoreGameLogic\Feature\Spielzug\Event\JobOfferWasAccepted;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\JobRequirements;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\Gehalt;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseDefinition;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphasenId;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphaseTypeEnum;

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
        CardFinder::getInstance()->overrideCardsForTesting([
            PileId::JOBS_PHASE_1->value => [
                "j0" => new JobCardDefinition(
                    id: new CardId('j0'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'Fachinformatikerin',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new Gehalt(34000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                        bildungKompetenzsteine: 2,
                    ),
                ),
                "j1" => new JobCardDefinition(
                    id: new CardId('j1'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'Pflegefachkraft (not eligible)',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new Gehalt(25000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                        bildungKompetenzsteine: 2,
                    ),
                ),
                "j2" => new JobCardDefinition(
                    id: new CardId('j2'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'Taxifahrer:in',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new Gehalt(18000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                        bildungKompetenzsteine: 2,
                    ),
                ),
            ]
        ]);
        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->player1));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var JobOffersWereRequested $actualEvent */
        $actualEvent = $stream->findLast(JobOffersWereRequested::class);
        expect($actualEvent->player)->toEqual($this->player1)
            ->and($actualEvent->jobs)->toEqual([]);
    });

    it('returns 3 jobs with fulfilled requirements', function (){
        CardFinder::getInstance()->overrideCardsForTesting([
            PileId::JOBS_PHASE_1->value => [
                "j0" => new JobCardDefinition(
                    id: new CardId('j0'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'Fachinformatikerin',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new Gehalt(34000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                    ),
                ),
                "j1" => new JobCardDefinition(
                    id: new CardId('j1'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'Pflegefachkraft (not eligible)',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new Gehalt(25000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                        bildungKompetenzsteine: 2,
                    ),
                ),
                "j2" => new JobCardDefinition(
                    id: new CardId('j2'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'Taxifahrer:in',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new Gehalt(18000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                    ),
                ),
                "j3" => new JobCardDefinition(
                    id: new CardId('j3'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'Testjob444',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new Gehalt(18000),
                    requirements: new JobRequirements(),
                ),
            ]
        ]);
        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->player1));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var JobOffersWereRequested $actualEvent */
        $actualEvent = $stream->findLast(JobOffersWereRequested::class);
        expect($actualEvent->player)->toEqual($this->player1)
            ->and(count($actualEvent->jobs))->toBe(3)
            ->and($actualEvent->jobs)->toContainEqual(new CardId('j0'))
            ->and($actualEvent->jobs)->toContainEqual(new CardId('j2'))
            ->and($actualEvent->jobs)->toContainEqual(new CardId('j3'));
    });


    it('returns 2 jobs with fulfilled requirements if that is all that is available', function (){
        CardFinder::getInstance()->overrideCardsForTesting([
            PileId::JOBS_PHASE_1->value => [
                "j0" => new JobCardDefinition(
                    id: new CardId('j0'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'Fachinformatikerin',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new Gehalt(34000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                    ),
                ),
                "j1" => new JobCardDefinition(
                    id: new CardId('j1'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'Pflegefachkraft (not eligible)',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new Gehalt(25000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                        bildungKompetenzsteine: 2,
                    ),
                ),
                "j2" => new JobCardDefinition(
                    id: new CardId('j2'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'Taxifahrer:in',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new Gehalt(18000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                    ),
                ),
                "j3" => new JobCardDefinition(
                    id: new CardId('j3'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'Testjob444',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new Gehalt(18000),
                    requirements: new JobRequirements(
                        bildungKompetenzsteine: 3
                    ),
                ),
            ]
        ]);
        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->player1));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var JobOffersWereRequested $actualEvent */
        $actualEvent = $stream->findLast(JobOffersWereRequested::class);
        expect($actualEvent->player)->toEqual($this->player1)
            ->and(count($actualEvent->jobs))->toBe(2)
            ->and($actualEvent->jobs)->toContainEqual(new CardId('j0'))
            ->and($actualEvent->jobs)->toContainEqual(new CardId('j2'));
    });

});
describe('handleAcceptJobOffer', function () {
    it('throws an exception if player did not request job offers this turn', function () {
        CardFinder::getInstance()->overrideCardsForTesting([
            PileId::JOBS_PHASE_1->value => [
                "j3" => new JobCardDefinition(
                    id: new CardId('j3'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'not offered',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new Gehalt(18000),
                    requirements: new JobRequirements(),
                ),
            ]
        ]);

        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->player1, new CardId('j3')));
    })->throws(\RuntimeException::class, 'You can only accept jobs that have been offered to you', 1748350449);

    it('throws an exception if job was not previously offered to player', function () {
        CardFinder::getInstance()->overrideCardsForTesting([
            PileId::JOBS_PHASE_1->value => [
                "j0" => new JobCardDefinition(
                    id: new CardId('j0'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'offered 1',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new Gehalt(34000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                    ),
                ),
                "j1" => new JobCardDefinition(
                    id: new CardId('j1'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'offered 2',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new Gehalt(25000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                    ),
                ),
                "j2" => new JobCardDefinition(
                    id: new CardId('j2'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'offered 3',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new Gehalt(18000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                    ),
                ),
                "j3" => new JobCardDefinition(
                    id: new CardId('j3'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'not offered',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new Gehalt(18000),
                    requirements: new JobRequirements(),
                ),
            ]
        ]);

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->player1));

        /** @var GameEvents $stream */
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        $offeredJobs = array_map(fn ($job) => $job->value, $stream->findLast(JobOffersWereRequested::class)->jobs);
        $jobThatWasNotOffered = array_values(array_filter(['j0', 'j1', 'j2', 'j3'], fn ($id) => !in_array($id, $offeredJobs)))[0];

        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->player1, new CardId($jobThatWasNotOffered)));
    })->throws(\RuntimeException::class, 'You can only accept jobs that have been offered to you', 1748350449);

    it('Showing JobOffers costs 1 Zeitstein', function () {
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->player1))->toBe(3);
        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->player1));
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->player1))->toBe(2);
    });

    it('permanently removes 1 Zeitstein while the player has a job', function () {
        CardFinder::getInstance()->overrideCardsForTesting([
            PileId::BILDUNG_PHASE_1->value => [],
            PileId::FREIZEIT_PHASE_1->value => [],
            PileId::JOBS_PHASE_1->value => [
                "j0" => new JobCardDefinition(
                    id: new CardId('j0'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'offered 1',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new Gehalt(34000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                    ),
                ),
            ]
        ]);

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->player1));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->player1, new CardId('j0')));
        $this->coreGameLogic->handle(
            $this->gameId,
            ChangeKonjunkturphase::create()->withFixedKonjunkturphaseForTesting(new KonjunkturphaseDefinition(
                id: KonjunkturphasenId::create(161),
                type: KonjunkturphaseTypeEnum::AUFSCHWUNG,
                description: 'no changes',
                additionalEvents: '',
                leitzins: 5,
                kompetenzbereiche: [],
                auswirkungen: []
            )));

        /** @var GameEvents $stream */
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->player1))->toBe(2);
    })->skip('not yet implemented');

    it('returns 1 Zeitstein to the player after quitting the job (in the next Konjunkturphase)', function () {
        // TODO clarify if this is how it should work
    })->skip('not yet implemented');

    it('saves the correct Job and Gehalt', function () {
        CardFinder::getInstance()->overrideCardsForTesting([
            PileId::JOBS_PHASE_1->value => [
                "j0" => new JobCardDefinition(
                    id: new CardId('j0'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'offered 1',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new Gehalt(34000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                    ),
                ),
            ]
        ]);

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->player1));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->player1, new CardId('j0')));

        /** @var GameEvents $stream */
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect($stream->FindLast(JobOfferWasAccepted::class)->gehalt->value)->toBe(34000)
            ->and($stream->FindLast(JobOfferWasAccepted::class)->job->value)->toBe('j0');
    });
});
