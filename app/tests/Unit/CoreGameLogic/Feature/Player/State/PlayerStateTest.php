<?php
declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Player\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\CardOrder;
use Domain\CoreGameLogic\Feature\Spielzug\Command\AcceptJobOffer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ChangeLebenszielphase;
use Domain\CoreGameLogic\Feature\Spielzug\Command\DoMinijob;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\RequestJobOffers;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SkipCard;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LebenszielphaseWasChanged;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\JobRequirements;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
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
           ]
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





//       $this->addCardsOnTopOfPile($cardsForTesting, $this->pileIdBildung);
//       $this->coreGameLogic->handle($this->gameId, ActivateCard::create(playerId: $this->players[0], categoryId: CategoryId::BILDUNG_UND_KARRIERE));

//       $this->coreGameLogic->handle($this->gameId, ChangeLebenszielphase::create(playerId: $this->players[0]));
     $stream = $this->coreGameLogic->getGameEvents($this->gameId);

       expect(PlayerState::getCurrentLebenszielphaseDefinitionForPlayer($stream,$this->players[0]))->phase->toEqual(2)
           ->and(PlayerState::getCurrentLebenszielphaseDefinitionForPlayer($stream, $this->players[1]))->phase->toEqual(1);
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
    })->throws(RuntimeException::class, 'Player doesNotExist does not exist', 1748432811);
});

describe('getGuthabenForPlayer', function () {
    it('returns the correct number', function () {
        $card1 = new KategorieCardDefinition(
            id: CardId::fromString('buk0'),
            pileId: PileId::BILDUNG_PHASE_1,
            title: 'test1',
            description: 'test',
            resourceChanges: new ResourceChanges(
                guthabenChange: new MoneyAmount(-500)
            )
        );
        $card2 = new KategorieCardDefinition(
            id: CardId::fromString('buk1'),
            pileId: PileId::BILDUNG_PHASE_1,
            title: 'test1',
            description: 'test',
            resourceChanges: new ResourceChanges(
                guthabenChange: new MoneyAmount(-100)
            )
        );
        CardFinder::getInstance()->overrideCardsForTesting([
            PileId::BILDUNG_PHASE_1->value => [
                "buk0" => $card1,
                "buk1" => $card2,
            ],
            PileId::FREIZEIT_PHASE_1->value => [],
            PileId::JOBS_PHASE_1->value => [],
            PileId::MINIJOBS_PHASE_1->value => [],
        ]);

        $this->coreGameLogic->handle(
            $this->gameId,
            ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[0]));
        $this->coreGameLogic->handle(
            $this->gameId,
            ActivateCard::create($this->players[1], CategoryId::BILDUNG_UND_KARRIERE));
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getGuthabenForPlayer($stream, $this->players[0]))->toEqual(new MoneyAmount(49500))
            ->and(PlayerState::getGuthabenForPlayer($stream, $this->players[1]))->toEqual(new MoneyAmount(49900));
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
    expect(PreGameState::lebenszielForPlayer($gameEvents, $this->players[0])->phases[0]->placedKompetenzsteineBildung)->toBe(1)
        ->and(PlayerState::getBildungsKompetenzsteine($gameEvents, $this->players[0]))->toBe(1)
        ->and(PlayerState::getZeitsteinePlacedForCurrentKonjunkturphaseInCategory($gameEvents, $this->players[0], CategoryId::BILDUNG_UND_KARRIERE))->toBe(1)
        ->and(PreGameState::lebenszielForPlayer($gameEvents, $this->players[1])->phases[0]->placedKompetenzsteineBildung)->toBe(0)
        ->and(PlayerState::getBildungsKompetenzsteine($gameEvents, $this->players[1]))->toBe(0)
        ->and(PlayerState::getZeitsteinePlacedForCurrentKonjunkturphaseInCategory($gameEvents, $this->players[1], CategoryId::BILDUNG_UND_KARRIERE))->toBe(0);

    //player 2 unchanged

    // change konjunkturphase
    $this->coreGameLogic->handle(
        $this->gameId,
        ChangeKonjunkturphase::create());

    $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
    // kompetenzen are saved for the lebensziel
    expect(PreGameState::lebenszielForPlayer($gameEvents, $this->players[0])->phases[0]->placedKompetenzsteineBildung)->toBe(1)
        ->and(PlayerState::getBildungsKompetenzsteine($gameEvents, $this->players[0]))->toBe(1)
        ->and(PlayerState::getZeitsteinePlacedForCurrentKonjunkturphaseInCategory($gameEvents, $this->players[0], CategoryId::BILDUNG_UND_KARRIERE))->toBe(0);
    // is 0 again because we are in the next konjunkturphase
});
