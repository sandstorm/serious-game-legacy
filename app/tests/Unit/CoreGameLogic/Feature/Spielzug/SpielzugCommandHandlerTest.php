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
use Domain\CoreGameLogic\Feature\Spielzug\State\CurrentPlayerAccessor;
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
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphasenId;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphaseTypeEnum;

beforeEach(function () {
    $this->setupBasicGame();
});


describe('handleSkipCard', function () {

    it('will consume a Zeitstein', function () {
        // Check the initial assumption of how many Zeitsteine the player has at the start of the test
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[0]))->toBe(6);

        $this->coreGameLogic->handle($this->gameId, new SkipCard(player: $this->players[0], category: CategoryId::BILDUNG_UND_KARRIERE));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[0]))->toBe(5);
    });

    it('Cannot skip twice', function () {
        $this->coreGameLogic->handle($this->gameId, new SkipCard(player: $this->players[0], category: CategoryId::BILDUNG_UND_KARRIERE));
        $this->coreGameLogic->handle($this->gameId, new SkipCard(player: $this->players[0], category: CategoryId::BILDUNG_UND_KARRIERE));
    })->throws(
        \RuntimeException::class,
        'Cannot skip Card: Du kannst nur eine Zeitsteinaktion pro Runde ausführen',
        1747325793);

    it('can only skip when it\'s the player\'s turn', function () {
        $this->coreGameLogic->handle($this->gameId, new SkipCard(player: $this->players[1], category: CategoryId::BILDUNG_UND_KARRIERE));
    })->throws(
        \RuntimeException::class,
        'Cannot skip Card: Du kannst Karten nur überspringen, wenn du dran bist',
        1747325793);

    it('cannot skip without a Zeitstein', function () {
        $cardsForTesting = [
            "cardToRemoveZeitsteine" => new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine'),
                pileId: $this->pileIdBildung,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -5,
                ),
            ),
        ];
        $this->addCardsOnTopOfPile($cardsForTesting, $this->pileIdBildung);

        // Check the initial assumption of how many Zeitsteine the player has at the start of the test
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[0]))->toBe(6);

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            player: $this->players[0],
            category: CategoryId::BILDUNG_UND_KARRIERE,
        ));

        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[0])
        );
        $this->coreGameLogic->handle($this->gameId, new SkipCard(player: $this->players[1], category: CategoryId::SOZIALES_UND_FREIZEIT));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[1]));

        // confirm that the player has 0 Zeitsteine
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[0]))->toBe(0);

        $this->coreGameLogic->handle($this->gameId, new SkipCard(player: $this->players[0], category: CategoryId::BILDUNG_UND_KARRIERE));
    })->throws(\RuntimeException::class,
        'Cannot skip Card: Du hast nicht genug Ressourcen um die Karte zu überspringen', 1747325793);
});

describe('handleActivateCard', function () {

    it('will consume a Zeitstein (first turn)', function () {
        CardFinder::getInstance()->overrideCardsForTesting([
            PileId::BILDUNG_PHASE_1->value => [
                "testcard" => new KategorieCardDefinition(
                    id: new CardId('testcard'),
                    pileId: $this->pileIdBildung,
                    title: 'for testing',
                    description: '...',
                    resourceChanges: new ResourceChanges(
                        guthabenChange: -200,
                        bildungKompetenzsteinChange: +1,
                    ),
                ),
            ],
            PileId::FREIZEIT_PHASE_1->value => [],
            PileId::JOBS_PHASE_1->value => [],
        ]);
        // Check the initial assumption of how many Zeitsteine the player has at the start of the test
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[0]))->toBe(6);

        $this->coreGameLogic->handle(
            $this->gameId,
            ChangeKonjunkturphase::create()
        );

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(player: $this->players[0], category: CategoryId::BILDUNG_UND_KARRIERE));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[0]))->toBe(5);
    });

    it('will consume a Zeitstein (later turns)', function () {
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
        CardFinder::getInstance()->overrideCardsForTesting([
            PileId::BILDUNG_PHASE_1->value => [
                "testcard" => $cardToTest,
            ],
            PileId::FREIZEIT_PHASE_1->value => [],
            PileId::JOBS_PHASE_1->value => [],
        ]);

        $this->coreGameLogic->handle(
            $this->gameId,
            ChangeKonjunkturphase::create()->withFixedCardOrderForTesting(
                new CardOrder(pileId: $this->pileIdBildung, cards: [$skipThisCard, $cardToTest->id]),
            ));

        // Check the initial assumption of how many Zeitsteine the player has at the start of the test
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[0]))->toBe(6);

        $this->coreGameLogic->handle($this->gameId, new SkipCard(
            player: $this->players[0],
            category: CategoryId::BILDUNG_UND_KARRIERE,
        ));

        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[0])
        );

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            player: $this->players[1],
            category: CategoryId::BILDUNG_UND_KARRIERE,
        ));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[1]))->toBe(5);
    });

    it('will not consume a Zeitstein after skipping a Card', function () {
        $skipThisCard = new KategorieCardDefinition(
            id: new CardId('skipThisCard'),
            pileId: $this->pileIdBildung,
            title: 'skipped',
            description: '...',
            resourceChanges: new ResourceChanges(
                guthabenChange: -400,
                bildungKompetenzsteinChange: +2,
            ),
        );
        $cardToTest = new KategorieCardDefinition(
            id: new CardId('cardToTest'),
            pileId: $this->pileIdBildung,
            title: 'for testing',
            description: '...',
            resourceChanges: new ResourceChanges(
                guthabenChange: -200,
                bildungKompetenzsteinChange: +1,
            ),
        );
        $this->addCardsOnTopOfPile([$skipThisCard, $cardToTest], $this->pileIdBildung);

        // Check the initial assumption of how many Zeitsteine the player has at the start of the test
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[0]))->toBe(6);

        $this->coreGameLogic->handle($this->gameId, new SkipCard(
            player: $this->players[0],
            category: CategoryId::BILDUNG_UND_KARRIERE,
        ));

        // Skipping will consume a Zeitstein
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[0]))->toBe(5);

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            player: $this->players[0],
            category: CategoryId::BILDUNG_UND_KARRIERE,
        ));

        // Expect no additional Zeitstein being used
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[0]))->toBe(5);
    });

    it('Will not activate a card after skipping in a different category', function () {
        $cardToTest = new KategorieCardDefinition(
            id: new CardId('cardToTest'),
            pileId: $this->pileIdBildung,
            title: 'for testing',
            description: '...',
            resourceChanges: new ResourceChanges(
                guthabenChange: -200,
                bildungKompetenzsteinChange: +1,
            ),
        );
        $this->addCardsOnTopOfPile([$cardToTest], $this->pileIdBildung);

        $this->coreGameLogic->handle($this->gameId, new SkipCard(
            player: $this->players[0],
            category: CategoryId::SOZIALES_UND_FREIZEIT,
        ));
        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            player: $this->players[0],
            category: CategoryId::BILDUNG_UND_KARRIERE,
        ));
    })->throws(
        \RuntimeException::class,
        'Cannot activate Card: Du hast bereits eine Karte in einer anderen Kategorie übersprungen',
        1748951140);

    it('Will activate a card if requirements are met', function () {
        $cardToTest = new KategorieCardDefinition(
            id: new CardId('testcard'),
            pileId: $this->pileIdFreizeit,
            title: 'for testing',
            description: '...',
            resourceChanges: new ResourceChanges(
                guthabenChange: -200,
                freizeitKompetenzsteinChange: +1,
            ),
        );
        $this->addCardsOnTopOfPile([$cardToTest], $this->pileIdFreizeit);

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            player: $this->players[0],
            category: CategoryId::SOZIALES_UND_FREIZEIT,
        ));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        /** @var CardWasActivated $actualEvent */
        $actualEvent = $stream->findLast(CardWasActivated::class);
        // expect to lose an additional Zeitstein for activating the card
        $expectedResourceChanges = $cardToTest->resourceChanges->accumulate(new ResourceChanges(zeitsteineChange: -1));
        expect($actualEvent->cardId)->toEqual($cardToTest->id)
            ->and($actualEvent->playerId)->toEqual($this->players[0])
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
        $this->addCardsOnTopOfPile([$cardToTest], $this->pileIdBildung);

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            player: $this->players[1],
            category: CategoryId::BILDUNG_UND_KARRIERE,
        ));
    })->throws(\RuntimeException::class, 'Cannot activate Card: Du kannst Karten nur spielen, wenn du dran bist',
        1748951140);

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

        $this->addCardsOnTopOfPile([$cardToTest], $this->pileIdBildung);

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            player: $this->players[0],
            category: CategoryId::BILDUNG_UND_KARRIERE,
        ));
    })->throws(\RuntimeException::class,
        'Cannot activate Card: Du hast nicht genug Ressourcen um die Karte zu spielen',
        1748951140);

});

describe('handleRequestJobOffers', function () {
    it('throws an exception when the player does not fulfill the requirements for any job', function () {
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
        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var JobOffersWereRequested $actualEvent */
        $actualEvent = $stream->findLast(JobOffersWereRequested::class);
        expect($actualEvent->player)->toEqual($this->players[0])
            ->and($actualEvent->jobs)->toEqual([]);
    })->throws(\RuntimeException::class,
        'Cannot Request Job Offers: Du erfüllst momentan für keinen Job die Voraussetzungen', 1749043606);

    it('returns 3 jobs with fulfilled requirements', function () {
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
        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var JobOffersWereRequested $actualEvent */
        $actualEvent = $stream->findLast(JobOffersWereRequested::class);
        expect($actualEvent->player)->toEqual($this->players[0])
            ->and(count($actualEvent->jobs))->toBe(3)
            ->and($actualEvent->jobs)->toContainEqual(new CardId('j0'))
            ->and($actualEvent->jobs)->toContainEqual(new CardId('j2'))
            ->and($actualEvent->jobs)->toContainEqual(new CardId('j3'));
    });


    it('returns 2 jobs with fulfilled requirements if that is all that is available', function () {
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
        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var JobOffersWereRequested $actualEvent */
        $actualEvent = $stream->findLast(JobOffersWereRequested::class);
        expect($actualEvent->player)->toEqual($this->players[0])
            ->and(count($actualEvent->jobs))->toBe(2)
            ->and($actualEvent->jobs)->toContainEqual(new CardId('j0'))
            ->and($actualEvent->jobs)->toContainEqual(new CardId('j2'));
    });

    it('does not return more than 3 jobs', function () {
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
                    title: 'Pflegefachkraft',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new Gehalt(25000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
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
        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var JobOffersWereRequested $actualEvent */
        $actualEvent = $stream->findLast(JobOffersWereRequested::class);
        expect($actualEvent->player)->toEqual($this->players[0])
            ->and(count($actualEvent->jobs))->toBe(3);
    });

    it('costs 1 Zeitstein', function () {
        $jobs = [
            "testjob" => new JobCardDefinition(
                id: new CardId('testjob'),
                pileId: PileId::JOBS_PHASE_1,
                title: 'Fachinformatikerin',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new Gehalt(34000),
                requirements: new JobRequirements(),
            ),
        ];
        $this->addCardsOnTopOfPile($jobs, PileId::JOBS_PHASE_1);
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[0]))->toBe(6);
        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[0]))->toBe(5);
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

        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('j3')));
    })->throws(\RuntimeException::class, 'Du kannst nur einen Job annehmen, der dir vorgeschlagen wurde', 1749043636);

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

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));

        /** @var GameEvents $stream */
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        $offeredJobs = array_map(fn($job) => $job->value, $stream->findLast(JobOffersWereRequested::class)->jobs);
        $jobThatWasNotOffered = array_values(array_filter(['j0', 'j1', 'j2', 'j3'],
            fn($id) => !in_array($id, $offeredJobs)))[0];

        $this->coreGameLogic->handle($this->gameId,
            AcceptJobOffer::create($this->players[0], new CardId($jobThatWasNotOffered)));
    })->throws(\RuntimeException::class,
        'Cannot Accept Job Offer: Du kannst nur einen Job annehmen, der dir vorgeschlagen wurde', 1749043636);

    it('permanently removes 1 Zeitstein while the player has a job', function () {
        // Reaffirm the "normal" number of Zeitsteine (in case we change something and forget to adjust this test)
        /** @var GameEvents $stream */
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[0]))->toBe(6);

        // Add the job we want to accept
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

        // Request and accept the job
        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('j0')));

        // Expect two fewer Zeitsteine (-1 for the RequestJobOffers and one should now be permanently unavailable)
        /** @var GameEvents $stream */
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[0]))->toBe(4);

        // Start a new Konjunkturphase to see if the Zeitstein change persists
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
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[0]))->toBe(5);
    });

    it('returns 1 Zeitstein to the player after quitting the job (in the next Konjunkturphase)', function () {
        // TODO clarify if this is how it should work
    })->skip('not yet implemented');

    it('throws an exception if job requirements are not met', function () {
        // TODO discus -> this should not happen, since only eligible jobs are offered and nothing _should_ change until accepting the offer
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

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('j0')));

        /** @var GameEvents $stream */
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect($stream->FindLast(JobOfferWasAccepted::class)->gehalt->value)->toBe(34000)
            ->and($stream->FindLast(JobOfferWasAccepted::class)->job->value)->toBe('j0');
    });
});

describe('handleEndSpielzug', function () {
    it('throws an exception when it\'s not the players turn', function () {
        /** @var GameEvents $stream */
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(CurrentPlayerAccessor::forStream($stream))->toEqual($this->players[0]);

        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[1])
        );
    })->throws(
        \RuntimeException::class,
        'Cannot end spielzug: Du bist gerade nicht dran',
        1748946243
    );

    it('throws an exception when the player has not performed a Zeitsteinaktion this turn', function () {
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[0])
        );
    })->throws(
        \RuntimeException::class,
        'Cannot end spielzug: Du musst erst einen Zeitstein für eine Aktion ausgeben',
        1748946243
    );

    it('does not throw an exception when the player has not performed a Zeitsteinaktion this turn and has 0 Zeitsteine',
        function () {
            // Setup
            $cardToTest = new KategorieCardDefinition(
                id: new CardId('testcard'),
                pileId: $this->pileIdBildung,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    guthabenChange: -200,
                    zeitsteineChange: -5, // Remove all Zeitsteine
                    bildungKompetenzsteinChange: +1,
                ),
            );
            $this->addCardsOnTopOfPile([$cardToTest->id->value => $cardToTest], $cardToTest->pileId);
            // Play a turn that removes all Zeitsteine
            $this->coreGameLogic->handle(
                $this->gameId,
                ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE)
            );
            $this->coreGameLogic->handle(
                $this->gameId,
                new EndSpielzug($this->players[0])
            );
            // Player 2 does _something_ and finishes their turn
            $this->coreGameLogic->handle(
                $this->gameId,
                new SkipCard($this->players[1], CategoryId::SOZIALES_UND_FREIZEIT)
            );
            $this->coreGameLogic->handle(
                $this->gameId,
                new EndSpielzug($this->players[1])
            );

            // check prerequisite: player should have 0 Zeitsteine
            $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
            expect(PlayerState::getZeitsteineForPlayer($gameEvents, $this->players[0]))->toBe(0);

            // End Turn without spending a Zeitstein
            $this->coreGameLogic->handle(
                $this->gameId,
                new EndSpielzug($this->players[0])
            );

            // Expect player 2 to be the active player -> player one was able to end turn without spending a Zeitstein
            expect(CurrentPlayerAccessor::forStream($this->coreGameLogic->getGameEvents($this->gameId)))->toEqual($this->players[1]);
        });

    it('switches the current player', function () {
        $this->coreGameLogic->handle(
            $this->gameId,
            new SkipCard($this->players[0], CategoryId::SOZIALES_UND_FREIZEIT)
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[0])
        );
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(CurrentPlayerAccessor::forStream($stream))->toEqual($this->players[1]);
    });

    it('starts again with the first player when the last player ends their turn',
        function () {
            $this->coreGameLogic->handle(
                $this->gameId,
                new SkipCard($this->players[0], CategoryId::SOZIALES_UND_FREIZEIT)
            );
            $this->coreGameLogic->handle(
                $this->gameId,
                new EndSpielzug($this->players[0])
            );
            $this->coreGameLogic->handle(
                $this->gameId,
                new SkipCard($this->players[1], CategoryId::SOZIALES_UND_FREIZEIT)
            );
            $this->coreGameLogic->handle(
                $this->gameId,
                new EndSpielzug($this->players[1])
            );
            $stream = $this->coreGameLogic->getGameEvents($this->gameId);
            expect(CurrentPlayerAccessor::forStream($stream))->toEqual($this->players[0]);
        });
});
