<?php
declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Player\State;

use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
use Domain\CoreGameLogic\Feature\Spielzug\Command\AcceptJobOffer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\DoMinijob;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\QuitJob;
use Domain\CoreGameLogic\Feature\Spielzug\Command\RequestJobOffers;
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
use Tests\TestCase;

beforeEach(function () {
    $this->setupBasicGame();
});

describe('getJobForPlayer', function () {
    it('Player never accepted a job', function () {
        // expect returns null
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getJobForPlayer($stream, $this->players[0]))->toBeNull();
    });

    it('Player accepted a job and never quit', function () {
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
        expect(PlayerState::getJobForPlayer($stream, $this->players[0]))->toBe(CardFinder::getInstance()->getCardById(CardId::fromString('testJob')));
    });

    it('Player accepted a job and then quit', function () {
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
        expect(PlayerState::getJobForPlayer($stream, $this->players[0]))->toBeNull();

    });

    it('Player accepted a job, quit the job and accept a new job', function () {
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
            ]
        ]);

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('testJob')));
        $this->coreGameLogic->handle($this->gameId, QuitJob::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[0]));

        $minijobs = [
            "testMinijob" => new MinijobCardDefinition(
                id: new CardId('testMinijob'),
                pileId: PileId::MINIJOBS_PHASE_1,
                title: 'Softwaretester',
                description: 'Du arbeitest nebenbei als Softwaretester und bekommst einmalig Gehalt',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+500),
                ),
            )];

        $this->addCardsOnTopOfPile($minijobs, PileId::MINIJOBS_PHASE_1);

        $this->coreGameLogic->handle($this->gameId, DoMinijob::create($this->players[1]));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[1]));

        CardFinder::getInstance()->overrideCardsForTesting([
            PileId::JOBS_PHASE_1->value => [
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
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('testJob2')));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getJobForPlayer($stream, $this->players[0]))->toBe(CardFinder::getInstance()->getCardById(CardId::fromString('testJob2')));
    });

});

describe('getZeitsteineForPlayer', function () {
    it('returns the correct number', function () {
        $this->coreGameLogic->handle($this->gameId, new SkipCard($this->players[0], CategoryId::BILDUNG_UND_KARRIERE));
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[0]))->toBe(Configuration::INITIAL_AMOUNT_OF_ZEITSTEINE_FOR_TWO_PLAYERS - 1)
            ->and(PlayerState::getZeitsteineForPlayer($stream, $this->players[1]))->toBe(Configuration::INITIAL_AMOUNT_OF_ZEITSTEINE_FOR_TWO_PLAYERS);
    });

    it('Throws an exception if the player does not exist', function () {
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        PlayerState::getZeitsteineForPlayer($stream, PlayerId::fromString('doesNotExist'));
    })->throws(\RuntimeException::class, 'Player doesNotExist does not exist', 1748432811);
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
    })->throws(\RuntimeException::class, 'Player doesNotExist does not exist', 1747827331);
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
    expect(PlayerState::getBildungsKompetenzsteine($gameEvents, $this->players[0]))->toBe(0);
    expect(PlayerState::getZeitsteinePlacedForCurrentKonjunkturphaseInCategory($gameEvents, $this->players[0], CategoryId::BILDUNG_UND_KARRIERE))->toBe(0);

    // player 1 activates a card that gives them a bildungs kompetenzstein
    $this->coreGameLogic->handle(
        $this->gameId,
        ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE));

    $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

    // player 1
    expect(PreGameState::lebenszielForPlayer($gameEvents, $this->players[0])->phases[0]->placedKompetenzsteineBildung)->toBe(1);
    expect(PlayerState::getBildungsKompetenzsteine($gameEvents, $this->players[0]))->toBe(1);
    expect(PlayerState::getZeitsteinePlacedForCurrentKonjunkturphaseInCategory($gameEvents, $this->players[0], CategoryId::BILDUNG_UND_KARRIERE))->toBe(1);

    //player 2 unchanged
    expect(PreGameState::lebenszielForPlayer($gameEvents, $this->players[1])->phases[0]->placedKompetenzsteineBildung)->toBe(0);
    expect(PlayerState::getBildungsKompetenzsteine($gameEvents, $this->players[1]))->toBe(0);
    expect(PlayerState::getZeitsteinePlacedForCurrentKonjunkturphaseInCategory($gameEvents, $this->players[1], CategoryId::BILDUNG_UND_KARRIERE))->toBe(0);

    // change konjunkturphase
    $this->coreGameLogic->handle(
        $this->gameId,
        ChangeKonjunkturphase::create());

    $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
    // kompetenzen are saved for the lebensziel
    expect(PreGameState::lebenszielForPlayer($gameEvents, $this->players[0])->phases[0]->placedKompetenzsteineBildung)->toBe(1);
    expect(PlayerState::getBildungsKompetenzsteine($gameEvents, $this->players[0]))->toBe(1);
    // is 0 again because we are in the next konjunkturphase
    expect(PlayerState::getZeitsteinePlacedForCurrentKonjunkturphaseInCategory($gameEvents, $this->players[0], CategoryId::BILDUNG_UND_KARRIERE))->toBe(0);
});
