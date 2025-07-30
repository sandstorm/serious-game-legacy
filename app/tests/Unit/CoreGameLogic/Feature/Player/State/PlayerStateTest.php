<?php
declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Player\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
use Domain\CoreGameLogic\Feature\Spielzug\Command\AcceptJobOffer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\DoMinijob;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ChangeLebenszielphase;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\RequestJobOffers;
use Domain\CoreGameLogic\Feature\Spielzug\Command\QuitJob;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SkipCard;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\JobRequirements;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Card\ValueObject\LebenszielPhaseId;
use Domain\Definitions\Configuration\Configuration;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use RuntimeException;
use Tests\TestCase;

beforeEach(function () {
    /** @var TestCase $this */
    $this->setupBasicGame();
});

describe('getPlayerColorClass', function () {
    it('returns player color class', function () {
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);

        expect(PlayerState::getPlayerColorClass($stream, $this->players[0]))->toBe('player-color-1')
            ->and(PlayerState::getPlayerColorClass($stream, $this->players[1]))->toBe('player-color-2');
    });

    it('throws an exception if the player does not exist', function () {
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        PlayerState::getPlayerColorClass($stream, PlayerId::fromString('doesNotExist'));
    })->throws(RuntimeException::class, 'Player doesNotExist not found in player ordering', 1752835827);
});

describe('getJobForPlayer', function () {
    it('returns null if player never accepted a job', function () {
        /** @var TestCase $this */
        // expect returns null
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getJobForPlayer($stream, $this->players[0]))->toBeNull();
    });

    it('returns a job if player accepted a job and never quit', function () {
        /** @var TestCase $this */
        // expect return the correct JobCardDefinition
        $testJobs = [
            new JobCardDefinition(
                id: new CardId('testJob'),
                title: 'testtestetest',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($testJobs);

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('testJob')));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getJobForPlayer($stream, $this->players[1]))->toBeNull()
            ->and(PlayerState::getJobForPlayer($stream,
                $this->players[0]))->toBe(CardFinder::getInstance()->getCardById(CardId::fromString('testJob')));
    });

    it('returns null if Player accepted a job and then quit', function () {
        /** @var TestCase $this */
        // expect returns null
        $testJobs = [
            new JobCardDefinition(
                id: new CardId('testJob'),
                title: 'testtestetest',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($testJobs);

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('testJob')));
        $this->coreGameLogic->handle($this->gameId, QuitJob::create($this->players[0]));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getJobForPlayer($stream, $this->players[1]))->toBeNull()
            ->and(PlayerState::getJobForPlayer($stream, $this->players[0]))->toBeNull();
    });

    it('returns a new job if Player accepted a job, quit the job and accept a new job', function () {
        /** @var TestCase $this */
        // expect returns the latest accepted JobCardDefinition, from the new Job
        $testCards = [
            new JobCardDefinition(
                id: new CardId('testJob'),
                title: 'testtestetest',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
            new JobCardDefinition(
                id: new CardId('testJob2'),
                title: 'testtestetest',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($testCards);

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('testJob')));
        $this->coreGameLogic->handle($this->gameId, QuitJob::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[0]));

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[1]));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[1]));

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('testJob2')));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getJobForPlayer($stream, $this->players[0]))->toBe(CardFinder::getInstance()
            ->getCardById(CardId::fromString('testJob2')));
    });
});

describe('getCurrentLebenszielphaseDefinitionForPlayer', function () {
    it('returns 1 when nothing happend', function () {
        /** @var TestCase $this */
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getCurrentLebenszielphaseIdForPlayer($stream,
            $this->players[0]))->toEqual(LebenszielPhaseId::PHASE_1)
            ->and(PlayerState::getCurrentLebenszielphaseIdForPlayer($stream,
                $this->players[1]))->toEqual(LebenszielPhaseId::PHASE_1);
    });

    it('returns 2 for Player 1 after switching the phase and 1 for Player 2', function () {
        /** @var TestCase $this */
        /** @var GameEvents $stream */

        $cardsForTesting = [
            new KategorieCardDefinition(
                id: new CardId('cardToTest'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+500000),
                    zeitsteineChange: +4,
                    bildungKompetenzsteinChange: +5,
                    freizeitKompetenzsteinChange: +5,
                ),
            ),
            new JobCardDefinition(
                id: new CardId('testJob'),
                title: 'testtest',
                description: 'testest',
                gehalt: new MoneyAmount(80000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('testJob')));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[0]));

        $this->coreGameLogic->handle($this->gameId, DoMinijob::create($this->players[1]));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[1]));
        $this->coreGameLogic->handle($this->gameId,
            ActivateCard::create(playerId: $this->players[0], categoryId: CategoryId::BILDUNG_UND_KARRIERE));
        $this->coreGameLogic->handle($this->gameId, ChangeLebenszielphase::create(playerId: $this->players[0]));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);

        expect(PlayerState::getCurrentLebenszielphaseIdForPlayer($stream, $this->players[0]))->toEqual(LebenszielPhaseId::PHASE_2)
            ->and(PlayerState::getCurrentLebenszielphaseIdForPlayer($stream,
                $this->players[1]))->toEqual(LebenszielPhaseId::PHASE_1);
    });
});

describe('getZeitsteineForPlayer', function () {
    it('returns the correct number', function () {
        $this->coreGameLogic->handle($this->gameId, new SkipCard($this->players[0], CategoryId::BILDUNG_UND_KARRIERE));
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream,
            $this->players[0]))->toBe($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) - 1)
            ->and(PlayerState::getZeitsteineForPlayer($stream,
                $this->players[1]))->toBe($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2));
    });

    it('Throws an exception if the player does not exist', function () {
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        PlayerState::getZeitsteineForPlayer($stream, PlayerId::fromString('doesNotExist'));
    })->throws(RuntimeException::class, 'Player doesNotExist does not exist', 1748432811);
});

describe('getGuthabenForPlayer', function () {
    it('returns the correct number', function () {
        /** @var TestCase $this */
        $cardsForTesting = [
            new KategorieCardDefinition(
                id: new CardId('cardToRemoveGuthaben'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1000),
                ),
            ),
            new KategorieCardDefinition(
                id: new CardId('cardToRemoveGuthaben2'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-100),
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        $this->coreGameLogic->handle(
            $this->gameId,
            ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE));

        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[0]));

        $this->coreGameLogic->handle(
            $this->gameId,
            ActivateCard::create($this->players[1], CategoryId::BILDUNG_UND_KARRIERE));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getGuthabenForPlayer($stream,
            $this->players[0])->value)->toEqual(Configuration::STARTKAPITAL_VALUE - 1000)
            ->and(PlayerState::getGuthabenForPlayer($stream,
                $this->players[1])->value)->toEqual(Configuration::STARTKAPITAL_VALUE - 100);
    });

    it('Throws an exception if the player does not exist', function () {
        /** @var TestCase $this */
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        PlayerState::getGuthabenForPlayer($stream, PlayerId::fromString('doesNotExist'));
    })->throws(RuntimeException::class, 'Player doesNotExist does not exist', 1747827331);
});

describe('getKompetenzenForPlayer', function () {
    it('returns the correct number', function () {
        /** @var TestCase $this */
        $cardToTest = new KategorieCardDefinition(
            id: CardId::fromString('cardToTest'),
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
            title: 'setup Bildung',
            description: 'test',
            resourceChanges: new ResourceChanges(
                bildungKompetenzsteinChange: +1,
            )
        );
        $this->startNewKonjunkturphaseWithCardsOnTop([$cardToTest]);

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getBildungsKompetenzsteine($gameEvents, $this->players[0]))->toBe(0)
            ->and(PlayerState::getZeitsteinePlacedForCurrentKonjunkturphaseInCategory($gameEvents, $this->players[0], CategoryId::BILDUNG_UND_KARRIERE))->toBe(0);

        // player 1 activates a card that gives them a bildungs kompetenzstein
        $this->coreGameLogic->handle(
            $this->gameId,
            ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        // player 1
        expect(PlayerState::getBildungsKompetenzsteine($gameEvents, $this->players[0]))->toBe(1)
            ->and(PlayerState::getZeitsteinePlacedForCurrentKonjunkturphaseInCategory($gameEvents, $this->players[0], CategoryId::BILDUNG_UND_KARRIERE))->toBe(1)
            ->and(PlayerState::getBildungsKompetenzsteine($gameEvents, $this->players[1]))->toBe(0)
            ->and(PlayerState::getZeitsteinePlacedForCurrentKonjunkturphaseInCategory($gameEvents, $this->players[1], CategoryId::BILDUNG_UND_KARRIERE))->toBe(0);

        // player 2 unchanged

        // change konjunkturphase
        $this->coreGameLogic->handle(
            $this->gameId,
            ChangeKonjunkturphase::create());

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        // kompetenzen are saved for the lebensziel
        expect(PlayerState::getBildungsKompetenzsteine($gameEvents, $this->players[0]))->toBe(1)
            // is 0 again because we are in the next konjunkturphase
            ->and(PlayerState::getZeitsteinePlacedForCurrentKonjunkturphaseInCategory($gameEvents, $this->players[0], CategoryId::BILDUNG_UND_KARRIERE))->toBe(0);
    });

    it('resets after phase was changed by player', function () {
        /** @var TestCase $this */
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getBildungsKompetenzsteine($gameEvents, $this->players[0]))->toBe(0)
            ->and(PlayerState::getFreizeitKompetenzsteine($gameEvents, $this->players[0]))->toBe(0);

        $setupCards = [
            "cardToTest" => new KategorieCardDefinition(
                id: new CardId('cardToTest'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+500000),
                    zeitsteineChange: +4,
                    bildungKompetenzsteinChange: +5,
                    freizeitKompetenzsteinChange: +5,
                ),
            ),
        ];

        $this->startNewKonjunkturphaseWithCardsOnTop($setupCards);

        $this->coreGameLogic->handle($this->gameId,
            ActivateCard::create(playerId: $this->players[0], categoryId: CategoryId::BILDUNG_UND_KARRIERE));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getBildungsKompetenzsteine($gameEvents, $this->players[0]))->toBe(5)
            ->and(PlayerState::getFreizeitKompetenzsteine($gameEvents, $this->players[0]))->toBe(5);

        $this->coreGameLogic->handle($this->gameId, ChangeLebenszielphase::create(playerId: $this->players[0]));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        // returns 0 for the next phase
        expect(PlayerState::getBildungsKompetenzsteine($gameEvents, $this->players[0]))->toBe(0)
            ->and(PlayerState::getFreizeitKompetenzsteine($gameEvents, $this->players[0]))->toBe(0);
    });
});

describe("getCurrentTurnForPlayer", function () {
    it("returns 1 in the first round", function () {
        /** @var TestCase $this */
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getCurrentTurnForPlayer($gameEvents, $this->players[0])->value)->toBe(1);
    });

    it("increases after the player ends their turn", function () {
        /** @var TestCase $this */
        $this->coreGameLogic->handle($this->gameId, DoMinijob::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[0]));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getCurrentTurnForPlayer($gameEvents, $this->players[0])->value)->toBe(2)
            ->and(PlayerState::getCurrentTurnForPlayer($gameEvents, $this->players[1])->value)->toBe(1);
    });
});
