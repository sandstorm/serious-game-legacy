<?php
declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Player\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\CardOrder;
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
use Domain\Definitions\Card\Dto\MinijobCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Configuration\Configuration;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use RuntimeException;
use Tests\TestCase;

beforeEach(function () {
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
        // expect returns null
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getJobForPlayer($stream, $this->players[0]))->toBeNull();
    });

    it('returns a job if player accepted a job and never quit', function () {
        // expect return the correct JobCardDefinition
        CardFinder::getInstance()->overrideCardsForTesting([
            PileId::JOBS_PHASE_1->value => [
                "testJob" => new JobCardDefinition(
                    id: new CardId('testJob'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'testtestetest',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new MoneyAmount(34000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                    ),
                ),
            ]
        ]);

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('testJob')));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getJobForPlayer($stream, $this->players[1]))->toBeNull()
            ->and(PlayerState::getJobForPlayer($stream, $this->players[0]))->toBe(CardFinder::getInstance()->getCardById(CardId::fromString('testJob')));
    });

    it('returns null if Player accepted a job and then quit', function () {
        // expect returns null
        CardFinder::getInstance()->overrideCardsForTesting([
            PileId::JOBS_PHASE_1->value => [
                "testJob" => new JobCardDefinition(
                    id: new CardId('testJob'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'testtestetest',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new MoneyAmount(34000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                    ),
                ),
            ]
        ]);

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('testJob')));
        $this->coreGameLogic->handle($this->gameId, QuitJob::create($this->players[0]));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getJobForPlayer($stream, $this->players[1]))->toBeNull()
            ->and(PlayerState::getJobForPlayer($stream, $this->players[0]))->toBeNull();
    });

    it('returns a new job if Player accepted a job, quit the job and accept a new job', function () {
        // expect returns the latest accepted JobCardDefinition, from the new Job
        CardFinder::getInstance()->overrideCardsForTesting([
            PileId::JOBS_PHASE_1->value => [
                "testJob" => new JobCardDefinition(
                    id: new CardId('testJob'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'testtestetest',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new MoneyAmount(34000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                    ),
                ),
                "testJob2" => new JobCardDefinition(
                    id: new CardId('testJob2'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'testtestetest',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new MoneyAmount(34000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                    ),
                ),
            ]
        ]);

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('testJob')));
        $this->coreGameLogic->handle($this->gameId, QuitJob::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[0]));

        $this->coreGameLogic->handle($this->gameId,RequestJobOffers::create($this->players[1]));
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
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getCurrentLebenszielphaseDefinitionForPlayer($stream, $this->players[0]))->phase->toEqual(1)
            ->and(PlayerState::getCurrentLebenszielphaseDefinitionForPlayer($stream, $this->players[1]))->phase->toEqual(1);
    });

    it('returns 2 for Player 1 after switching the phase and 1 for Player 2', function () {
        /** @var TestCase $this */
        /** @var GameEvents $stream */

        CardFinder::getInstance()->overrideCardsForTesting([
            PileId::BILDUNG_PHASE_1->value => [
                "cardToTest" => new KategorieCardDefinition(
                    id: new CardId('cardToTest'),
                    pileId: $this->pileIdBildung,
                    title: 'for testing',
                    description: '...',
                    resourceChanges: new ResourceChanges(
                        guthabenChange: new MoneyAmount(+500000),
                        zeitsteineChange: +4,
                        bildungKompetenzsteinChange: +5,
                        freizeitKompetenzsteinChange: +5,
                    ),
                ),
            ],
            PileId::FREIZEIT_PHASE_1->value => [],
            PileId::MINIJOBS_PHASE_1->value => $this->getCardsForMinijobs(),
            PileId::JOBS_PHASE_1->value => [
                "testJob" => new JobCardDefinition(
                    id: new CardId('testJob'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'testtest',
                    description: 'testest',
                    gehalt: new MoneyAmount(80000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                    ),
                ),
            ],
            PileId::EREIGNISSE_BILDUNG_UND_KARRIERE_PHASE_1->value => $this->cardsEreignisseBildungUndKarriere,
        ]);

        $this->coreGameLogic->handle($this->gameId, ChangeKonjunkturphase::create()->withFixedCardOrderForTesting(
            new CardOrder(pileId: $this->pileIdBildung, cards: [CardId::fromString('cardToTest')]),
            new CardOrder(pileId: $this->pileIdFreizeit, cards: []),
            new CardOrder(pileId: $this->pileIdJobs, cards: [CardId::fromString('testJob')]),
            new CardOrder(pileId: $this->pileIdMinijobs, cards: array_map(fn($card) => $card->getId(), $this->getCardsForMinijobs())),
        ));

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('testJob')));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[0]));

        $this->coreGameLogic->handle($this->gameId, DoMinijob::create($this->players[1]));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[1]));
        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(playerId: $this->players[0], categoryId: CategoryId::BILDUNG_UND_KARRIERE));
        $this->coreGameLogic->handle($this->gameId, ChangeLebenszielphase::create(playerId: $this->players[0]));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);

        expect(PlayerState::getCurrentLebenszielphaseDefinitionForPlayer($stream, $this->players[0]))->phase->toEqual(2)
            ->and(PlayerState::getCurrentLebenszielphaseDefinitionForPlayer($stream, $this->players[1]))->phase->toEqual(1);
    });
});

describe('getZeitsteineForPlayer', function () {
    it('returns the correct number', function () {
        $this->coreGameLogic->handle($this->gameId, new SkipCard($this->players[0], CategoryId::BILDUNG_UND_KARRIERE));
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[0]))->toBe( $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) - 1)
            ->and(PlayerState::getZeitsteineForPlayer($stream, $this->players[1]))->toBe( $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2));
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
            "cardToRemoveGuthaben" => new KategorieCardDefinition(
                id: new CardId('cardToRemoveGuthaben'),
                pileId: $this->pileIdBildung,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1000),
                ),
            ),
            "cardToRemoveGuthabe2" => new KategorieCardDefinition(
                id: new CardId('cardToRemoveGuthaben2'),
                pileId: $this->pileIdBildung,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-100),
                ),
            ),
        ];
        $this->addCardsOnTopOfPile($cardsForTesting, $this->pileIdBildung);

        $this->coreGameLogic->handle(
            $this->gameId,
            ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE));

        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[0]));

        $this->coreGameLogic->handle(
            $this->gameId,
            ActivateCard::create($this->players[1], CategoryId::BILDUNG_UND_KARRIERE));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getGuthabenForPlayer($stream, $this->players[0])->value)->toEqual(Configuration::STARTKAPITAL_VALUE - 1000)
            ->and(PlayerState::getGuthabenForPlayer($stream, $this->players[1])->value)->toEqual(Configuration::STARTKAPITAL_VALUE - 100);
    });

    it('Throws an exception if the player does not exist', function () {
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        PlayerState::getGuthabenForPlayer($stream, PlayerId::fromString('doesNotExist'));
    })->throws(RuntimeException::class, 'Player doesNotExist does not exist', 1747827331);
});

test('getKompetenzenForPlayer', function () {
    $cardToTest = new KategorieCardDefinition(
        id: CardId::fromString('cardToTest'),
        pileId: PileId::BILDUNG_PHASE_1,
        title: 'setup Bildung',
        description: 'test',
        resourceChanges: new ResourceChanges(
            bildungKompetenzsteinChange: +1,
        )
    );
    $this->addCardsOnTopOfPile([$cardToTest], PileId::BILDUNG_PHASE_1);

    $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
    expect(PlayerState::getBildungsKompetenzsteine($gameEvents, $this->players[0]))->toBe(0)
        ->and(PlayerState::getZeitsteinePlacedForCurrentKonjunkturphaseInCategory($gameEvents, $this->players[0], CategoryId::BILDUNG_UND_KARRIERE))->toBe(0);

    // player 1 activates a card that gives them a bildungs kompetenzstein
    $this->coreGameLogic->handle(
        $this->gameId,
        ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE));

    $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

    // player 1
    expect(PlayerState::lebenszielForPlayer($gameEvents, $this->players[0])->phases[0]->placedKompetenzsteineBildung)->toBe(1)
        ->and(PlayerState::getBildungsKompetenzsteine($gameEvents, $this->players[0]))->toBe(1)
        ->and(PlayerState::getZeitsteinePlacedForCurrentKonjunkturphaseInCategory($gameEvents, $this->players[0], CategoryId::BILDUNG_UND_KARRIERE))->toBe(1)
        ->and(PlayerState::lebenszielForPlayer($gameEvents, $this->players[1])->phases[0]->placedKompetenzsteineBildung)->toBe(0)
        ->and(PlayerState::getBildungsKompetenzsteine($gameEvents, $this->players[1]))->toBe(0)
        ->and(PlayerState::getZeitsteinePlacedForCurrentKonjunkturphaseInCategory($gameEvents, $this->players[1], CategoryId::BILDUNG_UND_KARRIERE))->toBe(0);

    //player 2 unchanged

    // change konjunkturphase
    $this->coreGameLogic->handle(
        $this->gameId,
        ChangeKonjunkturphase::create());

    $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
    // kompetenzen are saved for the lebensziel
    expect(PlayerState::lebenszielForPlayer($gameEvents, $this->players[0])->phases[0]->placedKompetenzsteineBildung)->toBe(1)
        ->and(PlayerState::getBildungsKompetenzsteine($gameEvents, $this->players[0]))->toBe(1)
        // is 0 again because we are in the next konjunkturphase
        ->and(PlayerState::getZeitsteinePlacedForCurrentKonjunkturphaseInCategory($gameEvents, $this->players[0], CategoryId::BILDUNG_UND_KARRIERE))->toBe(0);
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
