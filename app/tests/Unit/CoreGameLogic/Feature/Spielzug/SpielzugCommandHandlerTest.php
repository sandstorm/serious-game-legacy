<?php

declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Spielzug;


use App\Livewire\Forms\TakeOutALoanForm;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior\ProvidesStockPriceChanges;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseHasEnded;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseWasChanged;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\StockPriceState;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Moneysheet\ValueObject\LoanId;
use Domain\CoreGameLogic\Feature\Spielzug\Command\AcceptJobOffer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ChangeLebenszielphase;
use Domain\CoreGameLogic\Feature\Spielzug\Command\BuyStocksForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\PutCardBackOnTopOfPile;
use Domain\CoreGameLogic\Feature\Spielzug\Command\DoMinijob;
use Domain\CoreGameLogic\Feature\Spielzug\Command\CompleteMoneysheetForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EnterLebenshaltungskostenForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EnterSteuernUndAbgabenForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\MarkPlayerAsReadyForKonjunkturphaseChange;
use Domain\CoreGameLogic\Feature\Spielzug\Command\QuitJob;
use Domain\CoreGameLogic\Feature\Spielzug\Command\RequestJobOffers;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SellStocksForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SkipCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\StartKonjunkturphaseForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\StartWeiterbildung;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SubmitAnswerForWeiterbildung;
use Domain\CoreGameLogic\Feature\Spielzug\Command\TakeOutALoanForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\LoanData;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasActivated;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasPutBackOnTopOfPile;
use Domain\CoreGameLogic\Feature\Spielzug\Event\JobOffersWereRequested;
use Domain\CoreGameLogic\Feature\Spielzug\Event\JobOfferWasAccepted;
use Domain\CoreGameLogic\Feature\Spielzug\Event\JobWasQuit;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LebenshaltungskostenForPlayerWereEntered;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LoanForPlayerWasCorrected;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LoanForPlayerWasEntered;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LebenszielphaseWasChanged;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasCompletedMoneysheetForCurrentKonjunkturphase;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerWasMarkedAsReadyForKonjunkturphaseChange;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SteuernUndAbgabenForPlayerWereEntered;
use Domain\CoreGameLogic\Feature\Spielzug\SpielzugCommandHandler;
use Domain\CoreGameLogic\Feature\Spielzug\State\CurrentPlayerAccessor;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\StockType;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\AnswerOption;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\JobRequirements;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\MinijobCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\Dto\WeiterbildungCardDefinition;
use Domain\Definitions\Card\ValueObject\AnswerId;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseFinder;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use RuntimeException;
use Tests\ComponentWithForm;
use Tests\TestCase;

@covers(SpielzugCommandHandler::class);

beforeEach(function () {
    /** @var TestCase $this */
    $this->setupBasicGame();
});

describe('handleSkipCard', function () {

    it('will consume a Zeitstein', function () {
        /** @var TestCase $this */
        // Check the initial assumption of how many Zeitsteine the player has at the start of the test
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream,
            $this->players[0]))->toBe($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2));

        $this->coreGameLogic->handle($this->gameId,
            new SkipCard(playerId: $this->players[0], categoryId: CategoryId::BILDUNG_UND_KARRIERE));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream,
            $this->players[0]))->toBe($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) - 1);
    });

    it('Cannot skip twice', function () {
        /** @var TestCase $this */
        $this->coreGameLogic->handle($this->gameId,
            new SkipCard(playerId: $this->players[0], categoryId: CategoryId::BILDUNG_UND_KARRIERE));
        $this->coreGameLogic->handle($this->gameId,
            new SkipCard(playerId: $this->players[0], categoryId: CategoryId::BILDUNG_UND_KARRIERE));
    })->throws(
        RuntimeException::class,
        'Du kannst nur eine Zeitsteinaktion pro Runde ausführen',
        1747325793);

    it('can only skip when it\'s the player\'s turn', function () {
        /** @var TestCase $this */
        $this->coreGameLogic->handle($this->gameId,
            new SkipCard(playerId: $this->players[1], categoryId: CategoryId::BILDUNG_UND_KARRIERE));
    })->throws(
        RuntimeException::class,
        'Cannot skip card: Du bist gerade nicht dran',
        1747325793);

    it('cannot skip without a Zeitstein', function () {
        /** @var TestCase $this */
        $cardsForTesting = [
            "cardToRemoveZeitsteine" => new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        // Check the initial assumption of how many Zeitsteine the player has at the start of the test
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream,
            $this->players[0]))->toBe($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2));

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            playerId: $this->players[0],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));

        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[0])
        );
        $this->coreGameLogic->handle($this->gameId,
            new SkipCard(playerId: $this->players[1], categoryId: CategoryId::SOZIALES_UND_FREIZEIT));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[1]));

        // confirm that the player has 0 Zeitsteine
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[0]))->toBe(0);

        $this->coreGameLogic->handle($this->gameId,
            new SkipCard(playerId: $this->players[0], categoryId: CategoryId::BILDUNG_UND_KARRIERE));
    })->throws(RuntimeException::class,
        'Cannot skip card: Du hast nicht genug Zeitsteine', 1747325793);

    it("cannot skip card when no free slots are available for this konjunkturphase", function () {
        /** @var TestCase $this */
        KonjunkturphaseFinder::getInstance()->overrideKonjunkturphaseDefinitionsForTesting([
            $this->konjunkturphaseDefinition,
        ]);
        $cardsToTest = [
            new KategorieCardDefinition(
                id: new CardId('testcard'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(0),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            new KategorieCardDefinition(
                id: new CardId('testcard1'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing 1',
                description: '...',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(0),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            new KategorieCardDefinition(
                id: new CardId('testcard2'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing 2',
                description: '...',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(0),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsToTest);
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        $events = $stream->findAllOfType(KonjunkturphaseWasChanged::class);
        expect(count($events))->toBe(2);

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            playerId: $this->players[0],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[0]));

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            playerId: $this->players[1],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[1]));

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            playerId: $this->players[0],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[0]));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(GamePhaseState::hasFreeTimeSlotsForCategory($gameEvents, CategoryId::BILDUNG_UND_KARRIERE))->toBeFalse();

        // this fails, no free slots available
        $this->coreGameLogic->handle($this->gameId, new SkipCard(
            playerId: $this->players[1],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));
    })->throws(RuntimeException::class,
        'Cannot skip card: Es gibt keine freien Zeitsteinslots mehr',
        1747325793);
});

describe('handlePutCardBackOnPile', function () {
    it('Throws exception if no card was skipped', function () {
        $this->coreGameLogic->handle($this->gameId, new PutCardBackOnTopOfPile(
            playerId: $this->players[0],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));
    })->throws(RuntimeException::class,
        'Karte ablegen nur möglich, wenn vorher eine Karte übersprungen wurde',
        1753362843);

    it('Throws exception if a card was skipped and the next one was played', function () {
        /** @var TestCase $this */
        $cardToTest = [
            // card to skip
            new KategorieCardDefinition(
                id: new CardId('testcard1'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(1000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            // card player can play
            new KategorieCardDefinition(
                id: new CardId('testcard2'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(2000),
                ),
            )
        ];

        $this->startNewKonjunkturphaseWithCardsOnTop($cardToTest);

        $this->coreGameLogic->handle($this->gameId,
            new SkipCard(playerId: $this->players[0], categoryId: CategoryId::BILDUNG_UND_KARRIERE));

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            playerId: $this->players[0],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));

        $this->coreGameLogic->handle($this->gameId, new PutCardBackOnTopOfPile(
            playerId: $this->players[0],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));
    })->throws(RuntimeException::class,
        'Karte ablegen nur möglich, wenn noch keine Karte gespielt oder zurückgelegt wurde',
        1753362843);

    it('Throws exception if a card was skipped and player can play the next card', function () {
        /** @var TestCase $this */
        $cardToTest = [
            // card to skip
            new KategorieCardDefinition(
                id: new CardId('testcard1'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(1000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            // card player can play
            new KategorieCardDefinition(
                id: new CardId('testcard2'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(2000),
                ),
            )
        ];

        $this->startNewKonjunkturphaseWithCardsOnTop($cardToTest);

        $this->coreGameLogic->handle($this->gameId,
            new SkipCard(playerId: $this->players[0], categoryId: CategoryId::BILDUNG_UND_KARRIERE));

        $this->coreGameLogic->handle($this->gameId, new PutCardBackOnTopOfPile(
            playerId: $this->players[0],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));

    })->throws(RuntimeException::class,
        'Du hast genug Ressourcen um die Karte zu spielen, du darfs sie nicht ablegen.',
        1753362843);

    it('Throws exception if a card was skipped and the next one was discarded', function () {
        /** @var TestCase $this */
        $cardToTest = [
            // card to skip
            new KategorieCardDefinition(
                id: new CardId('testcard1'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(1000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            // card player cannot play
            new KategorieCardDefinition(
                id: new CardId('testcard2'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount((Configuration::STARTKAPITAL_VALUE + 1) * -1),
                    bildungKompetenzsteinChange: +1,
                ),
            )
        ];

        $this->startNewKonjunkturphaseWithCardsOnTop($cardToTest);

        $this->coreGameLogic->handle($this->gameId,
            new SkipCard(playerId: $this->players[0], categoryId: CategoryId::BILDUNG_UND_KARRIERE));

        $this->coreGameLogic->handle($this->gameId, new PutCardBackOnTopOfPile(
            playerId: $this->players[0],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        $events = $stream->findAllOfType(CardWasPutBackOnTopOfPile::class);
        expect(count($events))->toBe(1);

        $this->coreGameLogic->handle($this->gameId, new PutCardBackOnTopOfPile(
            playerId: $this->players[0],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));

    })->throws(RuntimeException::class,
        'Karte ablegen nur möglich, wenn noch keine Karte gespielt oder zurückgelegt wurde',
        1753362843);

    it('Discarding a card works', function () {
        /** @var TestCase $this */
        $cardToTest = [
            // card to skip
            new KategorieCardDefinition(
                id: new CardId('testcard1'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(1000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            // card player cannot play
            new KategorieCardDefinition(
                id: new CardId('testcard2'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount((Configuration::STARTKAPITAL_VALUE + 1) * -1),
                    bildungKompetenzsteinChange: +1,
                ),
            )
        ];

        $this->startNewKonjunkturphaseWithCardsOnTop($cardToTest);

        $this->coreGameLogic->handle($this->gameId,
            new SkipCard(playerId: $this->players[0], categoryId: CategoryId::BILDUNG_UND_KARRIERE));

        $this->coreGameLogic->handle($this->gameId, new PutCardBackOnTopOfPile(
            playerId: $this->players[0],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        $events = $stream->findAllOfType(CardWasPutBackOnTopOfPile::class);

        expect(count($events))->toBe(1)
            ->and(PlayerState::getZeitsteineForPlayer($stream,
                $this->players[0]))->toBe($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) - 1);
    });
});

describe('handleActivateCard', function () {
    it('will consume a Zeitstein (first turn)', function () {
        /** @var TestCase $this */
        $cardsForTesting = [
            "testcard" => new KategorieCardDefinition(
                id: new CardId('testcard'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-200),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
        ];

        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        // Check the initial assumption of how many Zeitsteine the player has at the start of the test
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream,
            $this->players[0]))->toBe($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2));

        $this->coreGameLogic->handle(
            $this->gameId,
            ChangeKonjunkturphase::create()
                ->withFixedKonjunkturphaseForTesting($this->konjunkturphaseDefinition)
                ->withFixedCardOrderForTesting()
        );

        $this->coreGameLogic->handle($this->gameId,
            ActivateCard::create(playerId: $this->players[0], categoryId: CategoryId::BILDUNG_UND_KARRIERE));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream,
            $this->players[0]))->toBe($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) - 1);
    });

    it('will consume a Zeitstein (later turns)', function () {
        /** @var TestCase $this */
        $skipThisCard = new CardId('skipped');
        $cardsForTesting = [
            "testcard" => new KategorieCardDefinition(
                id: new CardId('testcard'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-200),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
        ];

        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        // Check the initial assumption of how many Zeitsteine the player has at the start of the test
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);

        expect(PlayerState::getZeitsteineForPlayer($stream,
            $this->players[0]))->toBe($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2));

        $this->coreGameLogic->handle($this->gameId, new SkipCard(
            playerId: $this->players[0],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));

        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[0])
        );

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            playerId: $this->players[1],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream,
            $this->players[1]))->toBe($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) - 1);
    });

    it('will not consume a Zeitstein after skipping a Card', function () {
        /** @var TestCase $this */
        $skipThisCard = new KategorieCardDefinition(
            id: new CardId('skipThisCard'),
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
            title: 'skipped',
            description: '...',
            resourceChanges: new ResourceChanges(
                guthabenChange: new MoneyAmount(-400),
                bildungKompetenzsteinChange: +2,
            ),
        );
        $cardToTest = new KategorieCardDefinition(
            id: new CardId('cardToTest'),
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
            title: 'for testing',
            description: '...',
            resourceChanges: new ResourceChanges(
                guthabenChange: new MoneyAmount(-200),
                bildungKompetenzsteinChange: +1,
            ),
        );
        $this->startNewKonjunkturphaseWithCardsOnTop([$skipThisCard, $cardToTest]);

        // Check the initial assumption of how many Zeitsteine the player has at the start of the test
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream,
            $this->players[0]))->toBe($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2));

        $this->coreGameLogic->handle($this->gameId, new SkipCard(
            playerId: $this->players[0],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));

        // Skipping will consume a Zeitstein
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream,
            $this->players[0]))->toBe($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) - 1);

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            playerId: $this->players[0],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));

        // Expect no additional Zeitstein being used
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream,
            $this->players[0]))->toBe($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) - 1);
    });

    it('Will not activate a card after skipping in a different category', function () {
        /** @var TestCase $this */
        $cardToTest = new KategorieCardDefinition(
            id: new CardId('cardToTest'),
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
            title: 'for testing',
            description: '...',
            resourceChanges: new ResourceChanges(
                guthabenChange: new MoneyAmount(-200),
                bildungKompetenzsteinChange: +1,
            ),
        );
        $this->startNewKonjunkturphaseWithCardsOnTop([$cardToTest]);

        $this->coreGameLogic->handle($this->gameId, new SkipCard(
            playerId: $this->players[0],
            categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
        ));
        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            playerId: $this->players[0],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));
    })->throws(
        RuntimeException::class,
        'Du hast bereits eine Karte in einer anderen Kategorie übersprungen',
        1748951140);

    it('Will not activate a card after another was used', function () {
        /** @var TestCase $this */
        // skip one card
        $this->coreGameLogic->handle($this->gameId, new SkipCard(
            playerId: $this->players[0],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));

        // play the next card
        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            playerId: $this->players[0],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));

        // play another card -> should fail
        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            playerId: $this->players[0],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));
    })->throws(
        RuntimeException::class,
        'Du hast bereits eine andere Aktion ausgeführt',
        1748951140);

    it('Will activate a card if requirements are met', function () {
        /** @var TestCase $this */
        $cardToTest = new KategorieCardDefinition(
            id: new CardId('testcard'),
            categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
            title: 'for testing',
            description: '...',
            resourceChanges: new ResourceChanges(
                guthabenChange: new MoneyAmount(-200),
                freizeitKompetenzsteinChange: +1,
            ),
        );
        $this->startNewKonjunkturphaseWithCardsOnTop([$cardToTest]);

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            playerId: $this->players[0],
            categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
        ));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        /** @var CardWasActivated $actualEvent */
        $actualEvent = $stream->findLast(CardWasActivated::class);
        // expect to lose an additional Zeitstein for activating the card
        $expectedResourceChanges = $cardToTest->getResourceChanges()->accumulate(new ResourceChanges(zeitsteineChange: -1));
        expect($actualEvent->cardId)->toEqual($cardToTest->getId())
            ->and($actualEvent->playerId)->toEqual($this->players[0])
            ->and($actualEvent->resourceChanges)->toEqual($expectedResourceChanges);
    });

    it("will not activate the card if it's not the players turn", function () {
        /** @var TestCase $this */
        $cardToTest = new KategorieCardDefinition(
            id: new CardId('testcard'),
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
            title: 'for testing',
            description: '...',
            resourceChanges: new ResourceChanges(
                guthabenChange: new MoneyAmount(-200),
                bildungKompetenzsteinChange: +1,
            ),
        );
        $this->startNewKonjunkturphaseWithCardsOnTop([$cardToTest]);

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            playerId: $this->players[1],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));
    })->throws(RuntimeException::class, 'Cannot activate Card: Du bist gerade nicht dran',
        1748951140);

    it("will not activate the card if the requirements are not met", function () {
        /** @var TestCase $this */
        $cardToTest = new KategorieCardDefinition(
            id: new CardId('testcard'),
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
            title: 'for testing',
            description: '...',
            resourceChanges: new ResourceChanges(
                guthabenChange: new MoneyAmount(-50001),
                bildungKompetenzsteinChange: +1,
            ),
        );

        $this->startNewKonjunkturphaseWithCardsOnTop([$cardToTest]);

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            playerId: $this->players[0],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));
    })->throws(RuntimeException::class,
        'Du hast nicht genug Ressourcen um die Karte zu spielen',
        1748951140);

    it("cannot activate card when no free slots are available for this konjunkturphase", function () {
        /** @var TestCase $this */
        $cardsToTest = [
            new KategorieCardDefinition(
                id: new CardId('testcard'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(0),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            new KategorieCardDefinition(
                id: new CardId('testcard1'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing 1',
                description: '...',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(0),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            new KategorieCardDefinition(
                id: new CardId('testcard2'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing 2',
                description: '...',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(0),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsToTest);
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        $events = $stream->findAllOfType(KonjunkturphaseWasChanged::class);
        expect(count($events))->toBe(2);

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            playerId: $this->players[0],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[0]));

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            playerId: $this->players[1],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[1]));

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            playerId: $this->players[0],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[0]));

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            playerId: $this->players[1],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[1]));

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            playerId: $this->players[0],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[0]));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(GamePhaseState::hasFreeTimeSlotsForCategory($gameEvents, CategoryId::BILDUNG_UND_KARRIERE))->toBeFalse();

        // this fails, no free slots available
        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            playerId: $this->players[1],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));
    })->throws(RuntimeException::class,
        'Cannot activate Card: Es gibt keine freien Zeitsteinslots mehr',
        1748951140);

});

describe('handleRequestJobOffers', function () {
    it('throws an exception when no free slots are available for this konjunkturphase', function () {
        /** @var TestCase $this */
        $cardsToTest = [
            new JobCardDefinition(
                id: new CardId('tj0'),
                title: 'Fachinformatikerin',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 0,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsToTest);
        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        /** @var JobOffersWereRequested $actualEvent */
        $actualEvent = $stream->findLast(JobOffersWereRequested::class);
        expect($actualEvent->playerId)->toEqual($this->players[0])
            ->and($actualEvent->jobs)->toContainEqual(new CardId('tj0'));

        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[0]));

        // repeat asking for a job until there are no free slots left
        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[1]));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[1]));

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[0]));

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[1]));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[1]));

        // this request fails, no free slots available
        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[0]));
    })->throws(RuntimeException::class,
        'Es gibt keine freien Zeitsteinslots mehr', 1749043606);


    it('returns 3 jobs', function () {
        /** @var TestCase $this */
        $cardsToTest = [
            new JobCardDefinition(
                id: new CardId('j0'),
                title: 'Fachinformatikerin',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
            new JobCardDefinition(
                id: new CardId('j1'),
                title: 'Pflegefachkraft (not eligible)',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(25000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsToTest);
        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var JobOffersWereRequested $actualEvent */
        $actualEvent = $stream->findLast(JobOffersWereRequested::class);
        expect($actualEvent->playerId)->toEqual($this->players[0])
            ->and(count($actualEvent->jobs))->toBe(3)
            ->and($actualEvent->jobs)->toContainEqual(new CardId('j0'))
            ->and($actualEvent->jobs)->toContainEqual(new CardId('j1'));
    });

    it('returns 2 jobs if that is all that is available', function () {
        /** @var TestCase $this */
        CardFinder::getInstance()->overrideCardsForTesting([
            "tj0" => new JobCardDefinition(
                id: new CardId('tj0'),
                title: 'Fachinformatikerin',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
            "tj3" => new JobCardDefinition(
                id: new CardId('tj3'),
                title: 'Testjob444',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(18000),
                requirements: new JobRequirements(
                    bildungKompetenzsteine: 3
                ),
            ),
        ]);
        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var JobOffersWereRequested $actualEvent */
        $actualEvent = $stream->findLast(JobOffersWereRequested::class);
        expect($actualEvent->playerId)->toEqual($this->players[0])
            ->and(count($actualEvent->jobs))->toBe(2)
            ->and($actualEvent->jobs)->toContainEqual(new CardId('tj0'))
            ->and($actualEvent->jobs)->toContainEqual(new CardId('tj3'));
    });

    it('does not return more than 3 jobs', function () {
        /** @var TestCase $this */
        $cardsToTest = [
            "j0" => new JobCardDefinition(
                id: new CardId('j0'),
                title: 'Fachinformatikerin',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
            "j1" => new JobCardDefinition(
                id: new CardId('j1'),
                title: 'Pflegefachkraft',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(25000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
            "j2" => new JobCardDefinition(
                id: new CardId('j2'),
                title: 'Taxifahrer:in',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(18000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
            "j3" => new JobCardDefinition(
                id: new CardId('j3'),
                title: 'Testjob444',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(18000),
                requirements: new JobRequirements(),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsToTest);
        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var JobOffersWereRequested $actualEvent */
        $actualEvent = $stream->findLast(JobOffersWereRequested::class);
        expect($actualEvent->playerId)->toEqual($this->players[0])
            ->and(count($actualEvent->jobs))->toBe(3);
    });

    it('costs 1 Zeitstein', function () {
        /** @var TestCase $this */
        $jobs = [
            "testjob" => new JobCardDefinition(
                id: new CardId('testjob'),
                title: 'Fachinformatikerin',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($jobs);
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream,
            $this->players[0]))->toBe($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2));
        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream,
            $this->players[0]))->toBe($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) - 1);
    });
});

describe('handleAcceptJobOffer', function () {
    it('throws an exception if player did not request job offers this turn', function () {
        /** @var TestCase $this */
        $cardsToTest = [
            "j3" => new JobCardDefinition(
                id: new CardId('j3'),
                title: 'not offered',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(18000),
                requirements: new JobRequirements(),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsToTest);

        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('j3')));
    })->throws(RuntimeException::class, 'Cannot Accept Job Offer: Dieser Job wurde dir noch nicht vorgeschlagen',
        1749043636);

    it('throws an exception if job was not previously offered to player', function () {
        /** @var TestCase $this */
        $cardsToTest = [
            "j0" => new JobCardDefinition(
                id: new CardId('j0'),
                title: 'offered 1',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
            "j1" => new JobCardDefinition(
                id: new CardId('j1'),
                title: 'offered 2',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(25000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
            "j2" => new JobCardDefinition(
                id: new CardId('j2'),
                title: 'offered 3',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(18000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
            "j3" => new JobCardDefinition(
                id: new CardId('j3'),
                title: 'not offered',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(18000),
                requirements: new JobRequirements(),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsToTest);

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));

        /** @var GameEvents $stream */
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        $offeredJobs = array_map(fn($job) => $job->value, $stream->findLast(JobOffersWereRequested::class)->jobs);
        $jobThatWasNotOffered = array_values(array_filter(['j0', 'j1', 'j2', 'j3'],
            fn($id) => !in_array($id, $offeredJobs)))[0];

        $this->coreGameLogic->handle($this->gameId,
            AcceptJobOffer::create($this->players[0], new CardId($jobThatWasNotOffered)));
    })->throws(RuntimeException::class,
        'Cannot Accept Job Offer: Dieser Job wurde dir noch nicht vorgeschlagen', 1749043636);

    it('permanently removes 1 Zeitstein while the player has a job and adds salary to gehalt', function () {
        /** @var TestCase $this */
        KonjunkturphaseFinder::getInstance()->overrideKonjunkturphaseDefinitionsForTesting([
            $this->konjunkturphaseDefinition,
        ]);

        // Reaffirm the "normal" number of Zeitsteine (in case we change something and forget to adjust this test)
        /** @var GameEvents $stream */
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream,
            $this->players[0]))->toBe($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2))
            ->and(PlayerState::getZeitsteineForPlayer($stream,
                $this->players[1]))->toBe($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2));

        // Add the job we want to accept
        $gehalt = new MoneyAmount(34000);
        $cardsForTesting = [
            new JobCardDefinition(
                id: new CardId('tj0'),
                title: 'offered 1',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: $gehalt,
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
            new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * ($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) - 3),
                ),
            ),
            new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine2'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * ($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) - 2),
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        // Request and accept the job
        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('tj0')));
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[0])
        );
        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[1]));
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[1])
        );

        // Expect two fewer Zeitsteine (-1 for the RequestJobOffers and one should now be permanently unavailable)
        /** @var GameEvents $stream */
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream,
            $this->players[0]))->toBe($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) - 2)
            ->and(PlayerState::getZeitsteineForPlayer($stream,
                $this->players[1]))->toBe($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) - 1);

        $this->makeSpielzugForPlayersAndChangeKonjunkturphase();

        // Start a new Konjunkturphase to see if the Zeitstein change persists
        $this->coreGameLogic->handle(
            $this->gameId,
            ChangeKonjunkturphase::create()->withFixedKonjunkturphaseForTesting($this->konjunkturphaseDefinition));

        /** @var GameEvents $stream */
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        // also check if the salary is correctly calculated
        $expectedGuthabenPlayer0 = Configuration::STARTKAPITAL_VALUE
            + PlayerState::getJobForPlayer($stream, $this->players[0])->getGehalt()->value
            - MoneySheetState::calculateLebenshaltungskostenForPlayer($stream, $this->players[0])->value
            - MoneySheetState::calculateSteuernUndAbgabenForPlayer($stream, $this->players[0])->value;

        $expectedGuthabenPlayer1 = Configuration::STARTKAPITAL_VALUE
            - MoneySheetState::calculateLebenshaltungskostenForPlayer($stream, $this->players[1])->value
            - MoneySheetState::calculateSteuernUndAbgabenForPlayer($stream, $this->players[1])->value;

        expect(PlayerState::getZeitsteineForPlayer($stream,
            $this->players[0]))->toBe($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) - 1)
            ->and(PlayerState::getZeitsteineForPlayer($stream,
                $this->players[1]))->toBe($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2))
            ->and(PlayerState::getGuthabenForPlayer($stream,
                $this->players[0]))->toEqual(new MoneyAmount($expectedGuthabenPlayer0))
            ->and(PlayerState::getGuthabenForPlayer($stream,
                $this->players[1]))->toEqual(new MoneyAmount($expectedGuthabenPlayer1));
    });

    it('still only binds one Zeitstein after switching jobs', function () {
        /** @var TestCase $this */
        // Reaffirm the "normal" number of Zeitsteine (in case we change something and forget to adjust this test)
        /** @var GameEvents $stream */
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream,
            $this->players[0]))->toBe($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2));

        // Add the job we want to accept
        $cardsForTesting = [
            new JobCardDefinition(
                id: new CardId('tj0'),
                title: 'offered 1',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
            new JobCardDefinition(
                id: new CardId('tj1'),
                title: 'offered 2',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(44000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        // Request and accept the job
        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('tj0')));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[0]));

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[1]));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[1]));

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('tj1')));

        // Expect three fewer Zeitsteine (-2 for the RequestJobOffers and one should now be permanently unavailable)
        /** @var GameEvents $stream */
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream,
            $this->players[0]))->toBe($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) - 3);

        // Start a new Konjunkturphase to see if the Zeitstein change persists
        $this->coreGameLogic->handle(
            $this->gameId,
            ChangeKonjunkturphase::create()->withFixedKonjunkturphaseForTesting($this->konjunkturphaseDefinition));

        /** @var GameEvents $stream */
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream,
            $this->players[0]))->toBe($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) - 1)
            ->and(PlayerState::getZeitsteineForPlayer($stream,
                $this->players[1]))->toBe($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2));
    });

    it('throws an exception if job requirements are not met', function () {
        /** @var TestCase $this */

        $this->startNewKonjunkturphaseWithCardsOnTop([
            new JobCardDefinition(
                id: new CardId('t0'),
                title: 'offered 1',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 4,
                ),
            ),
        ]);

        // Request and accept the job
        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('t0')));
    })->throws(
        RuntimeException::class,
        'Cannot Accept Job Offer: Du erfüllst nicht die Voraussetzungen für diesen Job',
        1749043636
    );

    it('saves the correct Job and Gehalt', function () {
        /** @var TestCase $this */
        $jobsForTesting = [
            new JobCardDefinition(
                id: new CardId('tj0'),
                title: 'offered 1',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($jobsForTesting);

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('tj0')));

        /** @var GameEvents $stream */
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect($stream->FindLast(JobOfferWasAccepted::class)->gehalt)->toEqual(new MoneyAmount(34000))
            ->and($stream->FindLast(JobOfferWasAccepted::class)->cardId->value)->toBe('tj0');
    });

    it('returns 1 Zeitstein to the player after quitting the job', function () {
        /** @var TestCase $this */
        $testJobs = [
            "testJob" => new JobCardDefinition(
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
        $eventsBeforeQuit = $this->coreGameLogic->getGameEvents($this->gameId);
        $zeitsteineBeforeQuit = PlayerState::getZeitsteineForPlayer($eventsBeforeQuit, $this->players[0]);
        $this->coreGameLogic->handle($this->gameId, QuitJob::create($this->players[0]));

        $events = $this->coreGameLogic->getGameEvents($this->gameId);

        $jobQuitEvent = $events->findLastOrNullWhere(fn($e
        ) => $e instanceof JobWasQuit && $e->playerId->equals($this->players[0]));
        expect($jobQuitEvent)->not->toBeNull()
            ->and(PlayerState::getZeitsteineForPlayer($events, $this->players[0]))->toBe($zeitsteineBeforeQuit + 1);
    });
});

describe('handleQuitJob', function () {
    it('throws an exception, when it\'s not the players turn', function () {
        /** @var TestCase $this */
        /** @var GameEvents $stream */
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(CurrentPlayerAccessor::forStream($stream))->toEqual($this->players[0]);

        $this->coreGameLogic->handle($this->gameId, QuitJob::create($this->players[1])
        );
    })->throws(
        RuntimeException::class,
        'Cannot Quit Job: Du bist gerade nicht dran',
        1752480505
    );

    it('throws an exception, when the player has no job', function () {
        /** @var TestCase $this */
        /** @var GameEvents $stream */

        $this->coreGameLogic->handle($this->gameId, QuitJob::create($this->players[0])
        );
    })->throws(
        RuntimeException::class,
        'Cannot Quit Job: Du hast keinen Job',
        1752480505
    );
});

describe('handleChangeLebenszielphase', function () {
    it('throws an exception when the player does not have enough BildungsKompetenzsteine to finish the phase',
        function () {
            /** @var TestCase $this */
            $cardsForTesting = [
                new KategorieCardDefinition(
                    id: new CardId('cardToTest'),
                    categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                    title: 'for testing',
                    description: '...',
                    resourceChanges: new ResourceChanges(
                        guthabenChange: new MoneyAmount(+50000),
                        freizeitKompetenzsteinChange: +5,
                    ),
                ),
            ];
            $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);
            $this->coreGameLogic->handle($this->gameId,
                ActivateCard::create(playerId: $this->players[0], categoryId: CategoryId::BILDUNG_UND_KARRIERE));
            $stream = $this->coreGameLogic->getGameEvents($this->gameId);
            expect(PlayerState::getBildungsKompetenzsteine($stream, $this->players[0]));
            $this->coreGameLogic->handle($this->gameId, ChangeLebenszielphase::create(playerId: $this->players[0])
            );
        })->throws(
        RuntimeException::class,
        'Cannot Change Lebensphase: Du hast nicht genug Kompetenzsteine in Bildung & Karriere',
        1751619852
    );

    it('throws an exception when the player does not have enough FreizeitKompetenzsteine to finish the phase',
        function () {
            /** @var TestCase $this */
            $cardsForTesting = [
                "cardToTest" => new KategorieCardDefinition(
                    id: new CardId('cardToTest'),
                    categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                    title: 'for testing',
                    description: '...',
                    resourceChanges: new ResourceChanges(
                        guthabenChange: new MoneyAmount(+50000),
                        bildungKompetenzsteinChange: +5,
                    ),
                ),
            ];
            $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);
            $this->coreGameLogic->handle($this->gameId,
                ActivateCard::create(playerId: $this->players[0], categoryId: CategoryId::SOZIALES_UND_FREIZEIT));
            $this->coreGameLogic->handle($this->gameId, ChangeLebenszielphase::create(playerId: $this->players[0])
            );
        })->throws(
        RuntimeException::class,
        'Cannot Change Lebensphase: Du hast nicht genug Kompetenzsteine in Freizeit & Sozial',
        1751619852
    );

    it('throws an exception when the player does not have enough Money to finish the phase', function () {
        /** @var TestCase $this */
        $cardsForTesting = [
            "cardToTest" => new KategorieCardDefinition(
                id: new CardId('cardToTest'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-50000),
                    zeitsteineChange: +4,
                    bildungKompetenzsteinChange: +5,
                    freizeitKompetenzsteinChange: +5,
                ),
            ),
        ];

        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);
        $this->coreGameLogic->handle($this->gameId,
            ActivateCard::create(playerId: $this->players[0], categoryId: CategoryId::BILDUNG_UND_KARRIERE));

        $this->coreGameLogic->handle($this->gameId, ChangeLebenszielphase::create(playerId: $this->players[0])
        );
    })->throws(
        RuntimeException::class,
        'Cannot Change Lebensphase: Du hast nicht genug Geld',
        1751619852
    );

    it('throws an exception when it\'s not the players turn', function () {
        /** @var TestCase $this */
        /** @var GameEvents $stream */
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(CurrentPlayerAccessor::forStream($stream))->toEqual($this->players[0]);

        $this->coreGameLogic->handle($this->gameId, ChangeLebenszielphase::create(playerId: $this->players[1])
        );
    })->throws(
        RuntimeException::class,
        'Cannot Change Lebensphase: Du bist gerade nicht dran',
        1751619852
    );

    it('changes the phase correctly', function () {
        $setupCards = [
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
        ];

        $this->startNewKonjunkturphaseWithCardsOnTop($setupCards);

        $this->coreGameLogic->handle($this->gameId,
            ActivateCard::create(playerId: $this->players[0], categoryId: CategoryId::BILDUNG_UND_KARRIERE));

        $eventsBefore = $this->coreGameLogic->getGameEvents($this->gameId)->findAllOfType(LebenszielphaseWasChanged::class);
        $this->coreGameLogic->handle($this->gameId, ChangeLebenszielphase::create(playerId: $this->players[0]));
        $eventsAfter = $this->coreGameLogic->getGameEvents($this->gameId)->findAllOfType(LebenszielphaseWasChanged::class);
        expect(count($eventsAfter))->toBe(count($eventsBefore) + 1);
    });

    it('saves the correct Ressources(BildungsKompetenztein, FreizeitKompetenzsteine, Guthaben)', function () {
        /** @var TestCase $this */
        /** @var GameEvents $stream */
        $cardsForTesting = [
            new KategorieCardDefinition(
                id: new CardId('cardToTest'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+50000),
                    bildungKompetenzsteinChange: +2,
                    freizeitKompetenzsteinChange: +3,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);
        $this->coreGameLogic->handle($this->gameId,
            ActivateCard::create(playerId: $this->players[0], categoryId: CategoryId::BILDUNG_UND_KARRIERE));
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        $resources = PlayerState::getResourcesForPlayer($stream, $this->players[0]);

        expect($resources->guthabenChange->value)->toBe(100000.0)
            ->and($resources->bildungKompetenzsteinChange)->toEqual(2)
            ->and($resources->freizeitKompetenzsteinChange)->toEqual(3);
    });
});

describe('handleDoMinijob', function () {
    it('throws an exception when it\'s not the players turn', function () {
        /** @var TestCase $this */
        $this->coreGameLogic->handle($this->gameId, DoMinijob::create($this->players[1]));
    })->throws(
        RuntimeException::class,
        'Cannot Do minijob: Du bist gerade nicht dran',
        1750854280
    );

    it('throws an exception when the player does not have enough Zeitsteine', function () {
        /** @var TestCase $this */
        $cardsForTesting = [
            new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);
        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            playerId: $this->players[0],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[0]))->toBe(0);
        $this->coreGameLogic->handle($this->gameId, DoMinijob::create($this->players[0]));
    })->throws(
        RuntimeException::class,
        'Cannot Do minijob: Du hast nicht genug Zeitsteine',
        1750854280
    );

    it('adds money to the players account after doing the minijob', function () {
        /** @var TestCase $this */
        $minijobs = [
            new MinijobCardDefinition(
                id: new CardId('testMinijob'),
                title: 'Softwaretester',
                description: 'Du arbeitest nebenbei als Softwaretester und bekommst einmalig Gehalt',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+500),
                ),
            )
        ];

        $this->startNewKonjunkturphaseWithCardsOnTop($minijobs);
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getGuthabenForPlayer($stream,
            $this->players[0])->value)->toEqual(Configuration::STARTKAPITAL_VALUE);
        $this->coreGameLogic->handle($this->gameId, DoMinijob::create($this->players[0]));
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getGuthabenForPlayer($stream,
            $this->players[0])->value)->toEqual(Configuration::STARTKAPITAL_VALUE + 500);
    });

    it('throws an exception when the player wants to do more than one Zeitsteinaktion', function () {
        /** @var TestCase $this */
        $this->coreGameLogic->handle($this->gameId, DoMinijob::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, DoMinijob::create($this->players[0]));
    })->throws(
        RuntimeException::class,
        'Du kannst nur eine Zeitsteinaktion pro Runde ausführen',
        1750854280
    );
});

describe('handleStartWeiterbildung', function () {
    it('throws an exception when it\'s not the players turn', function () {
        $this->coreGameLogic->handle($this->gameId, StartWeiterbildung::create($this->players[1]));
    })->throws(
        RuntimeException::class,
        'Du bist gerade nicht dran',
        1753087476
        );

    it('throws an exception when the player does not have enough Zeitsteine', function () {
        $cardsForTesting = [
            "cardToRemoveZeitsteine" => new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1, // remove 5 zeitsteine, so that the player has 0 Zeitsteine left after playing this card
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);
        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            playerId: $this->players[0],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));

        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[0])
        );

        $this->coreGameLogic->handle($this->gameId,
            new SkipCard(playerId: $this->players[1], categoryId: CategoryId::BILDUNG_UND_KARRIERE));

        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[1])
        );

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[0]))->toBe(0);
        $this->coreGameLogic->handle($this->gameId, StartWeiterbildung::create($this->players[0]));
    })->throws(
        RuntimeException::class,
        'Du hast nicht genug Zeitsteine',
        1753087476
        );

    it('throws an exception when the player wants to do more than one Zeitsteinaktion', function () {
        $this->coreGameLogic->handle($this->gameId, StartWeiterbildung::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, StartWeiterbildung::create($this->players[0]));
    })->throws(
        RuntimeException::class,
        'Du kannst nur eine Zeitsteinaktion pro Runde ausführen',
        1753087476
    );

    it('removes one Zeitstein when the player performs a weiterbildung', function () {
        $this->coreGameLogic->handle($this->gameId, StartWeiterbildung::create($this->players[0]));

        $updateStream = $this->coreGameLogic->getGameEvents($this->gameId);
        $updatedZeitsteine = PlayerState::getZeitsteineForPlayer($updateStream, $this->players[0]);
        expect($updatedZeitsteine)->toBe($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) - 1);
    });
});

describe('handleSubmitAnswerWeiterbildung', function () {
    it('throws an exception when it\'s not the players turn', function () {
        $this->coreGameLogic->handle($this->gameId, SubmitAnswerForWeiterbildung::create($this->players[1], AnswerId::fromString("a")));
    })->throws(
        RuntimeException::class,
        'Du bist gerade nicht dran',
        1753265973
    );

    it('throws an exception if no weiterbildung was started', function () {
        /** @var TestCase $this */
        $selectedAnswer = AnswerId::fromString('a');
        $this->coreGameLogic->handle($this->gameId, SubmitAnswerForWeiterbildung::create($this->players[0], $selectedAnswer));
    })->throws(
        RuntimeException::class,
        'Du hast diese Runde noch keine Weiterbildung gestartet',
        1753265973
    );

    it('throws an exception when player selects an answer that does not exist', function () {
        /** @var TestCase $this */
        $this->coreGameLogic->handle($this->gameId, StartWeiterbildung::create($this->players[0]));
        $invalidAnswerId = AnswerId::fromString('invalid-answer');
        $this->coreGameLogic->handle($this->gameId, SubmitAnswerForWeiterbildung::create($this->players[0], $invalidAnswerId));
    })->throws(
        RuntimeException::class,
        'Die gewählte Antwort existiert nicht.',
        1753265973
    );

    it('throws an exception when the player tries to answer twice', function () {
        /** @var TestCase $this */
        $this->coreGameLogic->handle($this->gameId, StartWeiterbildung::create($this->players[0]));
        $answerOption = AnswerId::fromString('a');
        $this->coreGameLogic->handle($this->gameId, SubmitAnswerForWeiterbildung::create($this->players[0], $answerOption));
        $this->coreGameLogic->handle($this->gameId, SubmitAnswerForWeiterbildung::create($this->players[0], $answerOption));
    })->throws(
        RuntimeException::class,
        'Du hast für diese Weiterbildung bereits eine Antwort abgegeben',
        1753265973
    );

    it('submits an valid answer for Weiterbildung', function () {
        $cardsForTesting = [
            "cardWeiterbildungTest" => new WeiterbildungCardDefinition(
                id: new CardId('cardWeiterbildungTest'),
                title: 'for testing weiterbildung',
                description: '...',
                answerOptions: [
                    new AnswerOption(new AnswerId("a"), "antwort 1", true),
                    new AnswerOption(new AnswerId("b"), "antwort 2"),
                    new AnswerOption(new AnswerId("c"), "antwort 3"),
                    new AnswerOption(new AnswerId("d"), "antwort 4"),
                ],
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        $this->coreGameLogic->handle($this->gameId, StartWeiterbildung::create($this->players[0]));

        $selectedAnswer = AnswerId::fromString('a');
        $this->coreGameLogic->handle($this->gameId, SubmitAnswerForWeiterbildung::create($this->players[0], $selectedAnswer));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $cardDefinitionOfLastWeiterbildung = PlayerState::getLastWeiterbildungCardDefinitionForPlayer($gameEvents, $this->players[0]);
        $answerEvent = PlayerState::getSubmittedAnswerForLatestWeiterbildungThisTurn($gameEvents, $this->players[0], $cardDefinitionOfLastWeiterbildung->getId());

        expect($answerEvent->playerId)->toEqual($this->players[0])
            ->and($answerEvent->cardId)->toEqual(new CardId('cardWeiterbildungTest'))
            ->and($answerEvent->wasCorrect)->toBeTrue()
            ->and(PlayerState::getBildungsKompetenzsteine($gameEvents, $this->players[0]))->toEqual(.5)
            ->and(PlayerState::getFreizeitKompetenzsteine($gameEvents, $this->players[0]))->toEqual(0)
            ->and(PlayerState::getZeitsteineForPlayer($gameEvents, $this->players[0]))->toEqual($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) - 1);
    });

    it('submits an invalid answer for Weiterbildung', function () {
        $cardsForTesting = [
            "cardWeiterbildungTest" => new WeiterbildungCardDefinition(
                id: new CardId('cardWeiterbildungTest'),
                title: 'for testing weiterbildung',
                description: '...',
                answerOptions: [
                    new AnswerOption(new AnswerId("a"), "antwort 1", true),
                    new AnswerOption(new AnswerId("b"), "antwort 2"),
                    new AnswerOption(new AnswerId("c"), "antwort 3"),
                    new AnswerOption(new AnswerId("d"), "antwort 4"),
                ],
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        $this->coreGameLogic->handle($this->gameId, StartWeiterbildung::create($this->players[0]));

        $selectedAnswer = AnswerId::fromString('b');
        $this->coreGameLogic->handle($this->gameId, SubmitAnswerForWeiterbildung::create($this->players[0], $selectedAnswer));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $cardDefinitionOfLastWeiterbildung = PlayerState::getLastWeiterbildungCardDefinitionForPlayer($gameEvents, $this->players[0]);
        $answerEvent = PlayerState::getSubmittedAnswerForLatestWeiterbildungThisTurn($gameEvents, $this->players[0], $cardDefinitionOfLastWeiterbildung->getId());

        expect($answerEvent->playerId)->toEqual($this->players[0])
            ->and($answerEvent->cardId)->toEqual(new CardId('cardWeiterbildungTest'))
            ->and($answerEvent->wasCorrect)->toBeFalse()
            ->and(PlayerState::getBildungsKompetenzsteine($gameEvents, $this->players[0]))->toEqual(0)
            ->and(PlayerState::getFreizeitKompetenzsteine($gameEvents, $this->players[0]))->toEqual(0)
            ->and(PlayerState::getZeitsteineForPlayer($gameEvents, $this->players[0]))->toEqual($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) - 1);
    });
});

describe('handleEndSpielzug', function () {
    it('throws an exception when it\'s not the players turn', function () {
        /** @var TestCase $this */
        /** @var GameEvents $stream */
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(CurrentPlayerAccessor::forStream($stream))->toEqual($this->players[0]);

        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[1])
        );
    })->throws(
        RuntimeException::class,
        'Cannot end spielzug: Du bist gerade nicht dran',
        1748946243
    );

    it('throws an exception when the player has not performed a Zeitsteinaktion this turn', function () {
        /** @var TestCase $this */
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[0])
        );
    })->throws(
        RuntimeException::class,
        'Cannot end spielzug: Du musst erst einen Zeitstein für eine Aktion ausgeben',
        1748946243
    );

    it('does not throw an exception when the player has not performed a Zeitsteinaktion this turn and has 0 Zeitsteine',
        function () {
            /** @var TestCase $this */
            // Setup
            $cardToTest = new KategorieCardDefinition(
                id: new CardId('testcard'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-200),
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                    // Remove all Zeitsteine
                    bildungKompetenzsteinChange: +1,
                ),
            );
            $this->startNewKonjunkturphaseWithCardsOnTop([$cardToTest]);
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
        /** @var TestCase $this */
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

    it('starts again with the first player when the last player ends their turn', function () {
        /** @var TestCase $this */
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

    it('ends current Konjunkturphase if no player has any Zeitsteine left', function () {
        /** @var TestCase $this */
        $cardsForTesting = [
            new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
            new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine2'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        $this->coreGameLogic->handle($this->gameId,
            ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[0]));

        $this->coreGameLogic->handle($this->gameId,
            ActivateCard::create($this->players[1], CategoryId::BILDUNG_UND_KARRIERE));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[1]));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect($gameEvents->findLast(KonjunkturphaseHasEnded::class) instanceof KonjunkturphaseHasEnded)->toBeTrue();
    });
});

describe('handleStartKonjunkturphaseForPlayer', function () {
    it('throws an exception if the player has already started this Konjunkturphase', function () {
        /** @var TestCase $this */
        $this->coreGameLogic->handle($this->gameId, StartKonjunkturPhaseForPlayer::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, StartKonjunkturPhaseForPlayer::create($this->players[0]));
    })->throws(RuntimeException::class,
        'Cannot start Konjunkturphase: Du hast diese Konjunkturphase bereits gestartet', 1751373528);

    it('works after a new Konjunkturphase has started', function () {
        /** @var TestCase $this */
        $cardsForTesting = [
            new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
            new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine2'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        $this->coreGameLogic->handle($this->gameId, StartKonjunkturPhaseForPlayer::create($this->players[1]));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(KonjunkturphaseState::hasPlayerStartedCurrentKonjunkturphase($gameEvents,
            $this->players[0]))->toBeFalse()
            ->and(KonjunkturphaseState::hasPlayerStartedCurrentKonjunkturphase($gameEvents,
                $this->players[1]))->toBeTrue();

        $this->coreGameLogic->handle($this->gameId, StartKonjunkturPhaseForPlayer::create($this->players[0]));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(KonjunkturphaseState::hasPlayerStartedCurrentKonjunkturphase($gameEvents, $this->players[0]))->toBeTrue()
            ->and(KonjunkturphaseState::hasPlayerStartedCurrentKonjunkturphase($gameEvents,
                $this->players[1]))->toBeTrue();

        $this->coreGameLogic->handle($this->gameId,
            ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[0]));

        $this->coreGameLogic->handle($this->gameId,
            ActivateCard::create($this->players[1], CategoryId::BILDUNG_UND_KARRIERE));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[1]));

        $this->coreGameLogic->handle($this->gameId, ChangeKonjunkturphase::create());

        $this->coreGameLogic->handle($this->gameId, StartKonjunkturPhaseForPlayer::create($this->players[1]));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(KonjunkturphaseState::hasPlayerStartedCurrentKonjunkturphase($gameEvents,
            $this->players[0]))->toBeFalse()
            ->and(KonjunkturphaseState::hasPlayerStartedCurrentKonjunkturphase($gameEvents,
                $this->players[1]))->toBeTrue();
    });
});

describe('handleCompleteMoneysheetForPlayer', function () {
    it('it works if the player has filled out all required fields', function () {
        /** @var TestCase $this */

        $cardsForTesting = [
            new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
            new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine2'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);
        $this->coreGameLogic->handle(
            $this->gameId,
            ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE)
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[0])
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            ActivateCard::create($this->players[1], CategoryId::BILDUNG_UND_KARRIERE)
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[1])
        );

        $this->coreGameLogic->handle(
            $this->gameId,
            EnterLebenshaltungskostenForPlayer::create($this->players[0],
                new MoneyAmount(Configuration::LEBENSHALTUNGSKOSTEN_MIN_VALUE))
        );

        $this->coreGameLogic->handle(
            $this->gameId,
            CompleteMoneysheetForPlayer::create($this->players[0])
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect($gameEvents->findLastOrNullWhere(
                fn($event) => $event instanceof PlayerHasCompletedMoneysheetForCurrentKonjunkturphase
                    && $event->playerId->equals($this->players[0])
                    && $event->year->equals(KonjunkturphaseState::getCurrentYear($gameEvents))
            ) === null
        )->toBeFalse('Moneysheet should have been completed');
    });

    it('it throws an error if the player did not yet fill out the money sheet', function () {
        /** @var TestCase $this */

        $cardsForTesting = [
            new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
            new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine2'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);
        $this->coreGameLogic->handle(
            $this->gameId,
            ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE)
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[0])
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            ActivateCard::create($this->players[1], CategoryId::BILDUNG_UND_KARRIERE)
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[1])
        );

        $this->coreGameLogic->handle(
            $this->gameId,
            CompleteMoneysheetForPlayer::create($this->players[0])
        );
    })->throws(RuntimeException::class,
        'Cannot complete money sheet: Du musst erst dein Money Sheet korrekt ausfüllen', 1751375431);
});
describe('handleEnterSteuernUndAbgabenForPlayer', function () {
    it('works for correct player input when the player has no job', function () {
        /** @var TestCase $this */

        $this->coreGameLogic->handle($this->gameId,
            EnterSteuernUndAbgabenForPlayer::create($this->players[0], new MoneyAmount(0)));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var SteuernUndAbgabenForPlayerWereEntered $actualEvent */
        $actualEvent = $gameEvents->findLast(SteuernUndAbgabenForPlayerWereEntered::class);
        expect($actualEvent->getPlayerInput())->toEqual(new MoneyAmount(0))
            ->and($actualEvent->getExpectedInput())->toEqual(new MoneyAmount(0))
            ->and($actualEvent->wasInputCorrect())->toBeTrue();
    });

    it('works for correct player input when the player has a job', function () {
        /** @var TestCase $this */

        $cardsToTest = [
            new JobCardDefinition(
                id: new CardId('tj0'),
                title: 'offered 1',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsToTest);
        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('tj0')));

        $playerInput = new MoneyAmount(8500);
        $this->coreGameLogic->handle($this->gameId,
            EnterSteuernUndAbgabenForPlayer::create($this->players[0], $playerInput));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var SteuernUndAbgabenForPlayerWereEntered $actualEvent */
        $actualEvent = $gameEvents->findLast(SteuernUndAbgabenForPlayerWereEntered::class);
        expect($actualEvent->getPlayerInput())->toEqual($playerInput, 'Player input should be ' . $playerInput)
            ->and($actualEvent->getExpectedInput())->toEqual($playerInput)
            ->and($actualEvent->wasInputCorrect())->toBeTrue();
    });

    it('works for incorrect player input when the player has no job', function () {
        /** @var TestCase $this */

        $this->coreGameLogic->handle($this->gameId,
            EnterSteuernUndAbgabenForPlayer::create($this->players[0], new MoneyAmount(200)));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var SteuernUndAbgabenForPlayerWereEntered $actualEvent */
        $actualEvent = $gameEvents->findLast(SteuernUndAbgabenForPlayerWereEntered::class);
        expect($actualEvent->getPlayerInput())->toEqual(new MoneyAmount(200))
            ->and($actualEvent->getExpectedInput())->toEqual(new MoneyAmount(0))
            ->and($actualEvent->wasInputCorrect())->toBeFalse();
    });

    it('works for incorrect player input when the player has a job', function () {
        /** @var TestCase $this */

        $cardsToTest = [
            new JobCardDefinition(
                id: new CardId('tj0'),
                title: 'offered 1',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsToTest);
        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('tj0')));

        $playerInput = new MoneyAmount(7500);
        $expectedValue = new MoneyAmount(8500);
        $this->coreGameLogic->handle($this->gameId,
            EnterSteuernUndAbgabenForPlayer::create($this->players[0], $playerInput));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var SteuernUndAbgabenForPlayerWereEntered $actualEvent */
        $actualEvent = $gameEvents->findLast(SteuernUndAbgabenForPlayerWereEntered::class);
        expect($actualEvent->getPlayerInput())->toEqual($playerInput, 'Player input should be ' . $playerInput)
            ->and($actualEvent->getExpectedInput())->toEqual($expectedValue)
            ->and($actualEvent->wasInputCorrect())->toBeFalse();
    });

    it('charges a fee after two incorrect entries', function () {
        /** @var TestCase $this */

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]))->toEqual(new MoneyAmount(50000));

        $this->coreGameLogic->handle($this->gameId,
            EnterSteuernUndAbgabenForPlayer::create($this->players[0], new MoneyAmount(200)));
        $this->coreGameLogic->handle($this->gameId,
            EnterSteuernUndAbgabenForPlayer::create($this->players[0], new MoneyAmount(300)));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        expect(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]))->toEqual(new MoneyAmount(49750));
    });

    it('charges no fee after one incorrect entries', function () {
        /** @var TestCase $this */

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]))->toEqual(new MoneyAmount(50000));

        $this->coreGameLogic->handle($this->gameId,
            EnterSteuernUndAbgabenForPlayer::create($this->players[0], new MoneyAmount(200)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]))->toEqual(new MoneyAmount(50000));
    });
});

describe('handleEnterLebenshaltungskostenForPlayer', function () {
    it('works for correct player input when the player has no job', function () {
        /** @var TestCase $this */

        $this->coreGameLogic->handle($this->gameId,
            EnterLebenshaltungskostenForPlayer::create($this->players[0], new MoneyAmount(5000)));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var LebenshaltungskostenForPlayerWereEntered $actualEvent */
        $actualEvent = $gameEvents->findLast(LebenshaltungskostenForPlayerWereEntered::class);
        expect($actualEvent->getPlayerInput())->toEqual(new MoneyAmount(5000))
            ->and($actualEvent->getExpectedInput())->toEqual(new MoneyAmount(5000))
            ->and($actualEvent->wasInputCorrect())->toBeTrue();
    });

    it('works for correct player input when the player has a job', function () {
        /** @var TestCase $this */

        $cardsToTest = [
            new JobCardDefinition(
                id: new CardId('tj0'),
                title: 'offered 1',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsToTest);
        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('tj0')));

        $playerInput = new MoneyAmount(11900);
        $this->coreGameLogic->handle($this->gameId,
            EnterLebenshaltungskostenForPlayer::create($this->players[0], $playerInput));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var LebenshaltungskostenForPlayerWereEntered $actualEvent */
        $actualEvent = $gameEvents->findLast(LebenshaltungskostenForPlayerWereEntered::class);
        expect($actualEvent->getPlayerInput())->toEqual($playerInput, 'Player input should be ' . $playerInput)
            ->and($actualEvent->getExpectedInput())->toEqual($playerInput)
            ->and($actualEvent->wasInputCorrect())->toBeTrue();
    });

    it('works for incorrect player input when the player has no job', function () {
        /** @var TestCase $this */

        $this->coreGameLogic->handle($this->gameId,
            EnterLebenshaltungskostenForPlayer::create($this->players[0], new MoneyAmount(200)));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var LebenshaltungskostenForPlayerWereEntered $actualEvent */
        $actualEvent = $gameEvents->findLast(LebenshaltungskostenForPlayerWereEntered::class);
        expect($actualEvent->getPlayerInput())->toEqual(new MoneyAmount(200))
            ->and($actualEvent->getExpectedInput())->toEqual(new MoneyAmount(5000))
            ->and($actualEvent->wasInputCorrect())->toBeFalse();
    });

    it('works for incorrect player input when the player has a job', function () {
        /** @var TestCase $this */

        $cardsToTest = [
            new JobCardDefinition(
                id: new CardId('tj0'),
                title: 'offered 1',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsToTest);
        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('tj0')));

        $playerInput = new MoneyAmount(7500);
        $expectedValue = new MoneyAmount(11900);
        $this->coreGameLogic->handle($this->gameId,
            EnterLebenshaltungskostenForPlayer::create($this->players[0], $playerInput));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var LebenshaltungskostenForPlayerWereEntered $actualEvent */
        $actualEvent = $gameEvents->findLast(LebenshaltungskostenForPlayerWereEntered::class);
        expect($actualEvent->getPlayerInput())->toEqual($playerInput, 'Player input should be ' . $playerInput)
            ->and($actualEvent->getExpectedInput())->toEqual($expectedValue)
            ->and($actualEvent->wasInputCorrect())->toBeFalse();
    });

    it('charges a fee after two incorrect entries', function () {
        /** @var TestCase $this */

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]))->toEqual(new MoneyAmount(50000));

        $this->coreGameLogic->handle($this->gameId,
            EnterLebenshaltungskostenForPlayer::create($this->players[0], new MoneyAmount(200)));
        $this->coreGameLogic->handle($this->gameId,
            EnterLebenshaltungskostenForPlayer::create($this->players[0], new MoneyAmount(300)));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        expect(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]))->toEqual(new MoneyAmount(49750));
    });

    it('charges no fee after one incorrect entries', function () {
        /** @var TestCase $this */

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]))->toEqual(new MoneyAmount(50000));

        $this->coreGameLogic->handle($this->gameId,
            EnterLebenshaltungskostenForPlayer::create($this->players[0], new MoneyAmount(200)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]))->toEqual(new MoneyAmount(50000));
    });
});

describe('handleMarkPlayerAsReadyForKonjunkturphaseChange', function () {

    it('throws an error if it\'s not the end of the konjunkturphase', function () {
        /** @var TestCase $this */
        $this->coreGameLogic->handle(
            $this->gameId,
            MarkPlayerAsReadyForKonjunkturphaseChange::create($this->players[0])
        );
    })->throws(RuntimeException::class,
        'Cannot mark player as ready: Die aktuelle Konjunkturphase ist noch nicht zu Ende', 1751373528);

    it('throws an error if the player has not completed the money sheet', function () {
        /** @var TestCase $this */

        $cardsForTesting = [
            new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
            new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine2'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);
        $this->coreGameLogic->handle(
            $this->gameId,
            ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE)
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[0])
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            ActivateCard::create($this->players[1], CategoryId::BILDUNG_UND_KARRIERE)
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[1])
        );

        $this->coreGameLogic->handle($this->gameId,
            EnterLebenshaltungskostenForPlayer::create($this->players[0],
                new MoneyAmount(Configuration::LEBENSHALTUNGSKOSTEN_MIN_VALUE)));

        $this->coreGameLogic->handle(
            $this->gameId,
            MarkPlayerAsReadyForKonjunkturphaseChange::create($this->players[0])
        );
    })->throws(RuntimeException::class, "Cannot mark player as ready: Du musst erst das Money Sheet korrekt ausfüllen",
        1751373528);

    it('marks the player as ready', function () {
        /** @var TestCase $this */

        $cardsForTesting = [
            new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
            new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine2'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);
        $this->coreGameLogic->handle(
            $this->gameId,
            ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE)
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[0])
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            ActivateCard::create($this->players[1], CategoryId::BILDUNG_UND_KARRIERE)
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[1])
        );

        $this->coreGameLogic->handle($this->gameId,
            EnterLebenshaltungskostenForPlayer::create($this->players[0],
                new MoneyAmount(Configuration::LEBENSHALTUNGSKOSTEN_MIN_VALUE)));

        $this->coreGameLogic->handle(
            $this->gameId,
            CompleteMoneysheetForPlayer::create($this->players[0])
        );

        $this->coreGameLogic->handle(
            $this->gameId,
            MarkPlayerAsReadyForKonjunkturphaseChange::create($this->players[0])
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect($gameEvents->findLastOrNullWhere(
                fn($event) => $event instanceof PlayerWasMarkedAsReadyForKonjunkturphaseChange
                    && $event->playerId->equals($this->players[0])
                    && $event->year->equals(KonjunkturphaseState::getCurrentYear($gameEvents))
            ) !== null)
            ->toBeTrue('The PlayerWasMarkedAsReadyForKonjunkturphaseChange event should exist for this player');
    });
});

describe('handleTakeOutALoanForPlayer', function () {
    it('player gets 250 € fine for entering loan data wrong', function () {
        $takeoutLoanFormComponent = new ComponentWithForm();
        $takeoutLoanFormComponent->mount(TakeOutALoanForm::class);

        /** @var TakeOutALoanForm $takeoutLoanForm */
        $takeoutLoanForm = $takeoutLoanFormComponent->form;
        $takeoutLoanForm->loanAmount = 10000;
        $takeoutLoanForm->totalRepayment = 12500;
        $takeoutLoanForm->repaymentPerKonjunkturphase = 600; // wrong value
        $takeoutLoanForm->guthaben = Configuration::STARTKAPITAL_VALUE;
        $takeoutLoanForm->zinssatz = 5;
        $takeoutLoanForm->loanId = LoanId::unique()->value;

        // player 0 takes out a loan
        $this->coreGameLogic->handle($this->gameId, TakeOutALoanForPlayer::create(
            $this->players[0],
            $takeoutLoanForm
        ));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var LoanForPlayerWasEntered $loanWasEnteredEvent */
        $loanWasEnteredEvent = $gameEvents->findLast(LoanForPlayerWasEntered::class);

        expect($loanWasEnteredEvent)->toBeInstanceOf(LoanForPlayerWasEntered::class)
            ->and($loanWasEnteredEvent->wasInputCorrect())->toBeFalse()
            ->and($loanWasEnteredEvent->getLoanData())->toEqual(new LoanData(
                loanAmount: new MoneyAmount(10000),
                totalRepayment: new MoneyAmount(12500),
                repaymentPerKonjunkturphase: new MoneyAmount(600),
            ))
            ->and($loanWasEnteredEvent->getExpectedLoanData())->toEqual(new LoanData(
                loanAmount: new MoneyAmount(10000),
                totalRepayment: new MoneyAmount(12500),
                repaymentPerKonjunkturphase: new MoneyAmount(625),
            ));

        // try again with also wrong values
        $this->coreGameLogic->handle($this->gameId, TakeOutALoanForPlayer::create(
            $this->players[0],
            $takeoutLoanForm
        ));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var LoanForPlayerWasCorrected $loanWasCorrectedEvent */
        $loanWasCorrectedEvent = $gameEvents->findLast(LoanForPlayerWasCorrected::class);

        expect($loanWasCorrectedEvent)->toBeInstanceOf(LoanForPlayerWasCorrected::class)
            ->and($loanWasCorrectedEvent->getLoanData())->toEqual(new LoanData(
                loanAmount: new MoneyAmount(10000),
                totalRepayment: new MoneyAmount(12500),
                repaymentPerKonjunkturphase: new MoneyAmount(625),
            ))
            ->and($loanWasCorrectedEvent->getResourceChanges($this->players[0])->guthabenChange)->toEqual(new MoneyAmount(-250))
            ->and(PlayerState::getGuthabenForPlayer($gameEvents,
                $this->players[0]))->toEqual(new MoneyAmount(Configuration::STARTKAPITAL_VALUE - 250));
    });
});

describe('handleBuyStocksForPlayer', function () {
    it('buying stocks works as expected', function () {
        // buy low risk stocks
        $amountOfStocks = 100;

        /** @var TestCase $this */
        $this->coreGameLogic->handle(
            $this->gameId,
            BuyStocksForPlayer::create(
                $this->players[0],
                StockType::LOW_RISK,
                $amountOfStocks
            )
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $currentPrice = StockPriceState::getCurrentStockPrice($gameEvents, StockType::LOW_RISK);
        $expectedSumOfAllStocks = new MoneyAmount($currentPrice->value * $amountOfStocks);
        $expectedGuthaben = new MoneyAmount(Configuration::STARTKAPITAL_VALUE - Configuration::INITIAL_STOCK_PRICE * $amountOfStocks);
        expect(PlayerState::getTotalValueOfAllStocksForPlayer($gameEvents,
            $this->players[0]))->toEqual($expectedSumOfAllStocks)
            ->and(PlayerState::getAmountOfAllStocksOfTypeForPlayer($gameEvents, $this->players[0],
                StockType::LOW_RISK))->toEqual($amountOfStocks)
            ->and(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]))->toEqual($expectedGuthaben)
            ->and(count($gameEvents->findAllOfType(ProvidesStockPriceChanges::class)))->toEqual(2);

        $highRiskPriceAfterFirstBuy = StockPriceState::getCurrentStockPrice($gameEvents, StockType::HIGH_RISK);

        // end zug for player 0
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[0])
        );
        // requst job offers for player 1
        $this->coreGameLogic->handle(
            $this->gameId,
            RequestJobOffers::create($this->players[1])
        );
        // end zug for player 1
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[1])
        );

        // buy some high risk stocks
        $amountOfStocksHighRisk = 50;
        $this->coreGameLogic->handle(
            $this->gameId,
            BuyStocksForPlayer::create(
                $this->players[0],
                StockType::HIGH_RISK,
                $amountOfStocksHighRisk
            )
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $currentPriceLowRisk = StockPriceState::getCurrentStockPrice($gameEvents, StockType::LOW_RISK);
        $currentPriceHighRisk = StockPriceState::getCurrentStockPrice($gameEvents, StockType::HIGH_RISK);
        $expectedSumOfAllStocks = new MoneyAmount($currentPriceLowRisk->value * $amountOfStocks + $currentPriceHighRisk->value * $amountOfStocksHighRisk);
        $expectedGuthaben = $expectedGuthaben->add(
            new MoneyAmount($highRiskPriceAfterFirstBuy->value * $amountOfStocksHighRisk * -1)
        );

        expect(PlayerState::getTotalValueOfAllStocksForPlayer($gameEvents,
            $this->players[0]))->toEqual($expectedSumOfAllStocks)
            ->and(PlayerState::getAmountOfAllStocksOfTypeForPlayer($gameEvents, $this->players[0],
                StockType::LOW_RISK))->toEqual($amountOfStocks)
            ->and(PlayerState::getAmountOfAllStocksOfTypeForPlayer($gameEvents, $this->players[0],
                StockType::HIGH_RISK))->toEqual($amountOfStocksHighRisk)
            ->and(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]))->toEqual($expectedGuthaben)
            ->and(count($gameEvents->findAllOfType(ProvidesStockPriceChanges::class)))->toEqual(3);
    });

    it('throws exception if player tries to buy more stocks than he can afford', function () {
        $amountOfStocks = intval(Configuration::STARTKAPITAL_VALUE / Configuration::INITIAL_STOCK_PRICE + 1);

        /** @var TestCase $this */
        $this->coreGameLogic->handle(
            $this->gameId,
            BuyStocksForPlayer::create(
                $this->players[0],
                StockType::LOW_RISK,
                $amountOfStocks
            )
        );
    })->throws(\RuntimeException::class, 'Du hast nicht genug Ressourcen', 1752066529);
});

describe('handleSellStocksForPlayer', function () {
    it('selling stocks works as expected', function () {
        $amountOfStocks = 100;

        /** @var TestCase $this */
        $this->coreGameLogic->handle(
            $this->gameId,
            BuyStocksForPlayer::create(
                $this->players[0],
                StockType::LOW_RISK,
                $amountOfStocks
            )
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        $expectedGuthaben = new MoneyAmount(Configuration::STARTKAPITAL_VALUE - Configuration::INITIAL_STOCK_PRICE * $amountOfStocks);
        expect(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]))->toEqual($expectedGuthaben)
            ->and(PlayerState::getAmountOfAllStocksOfTypeForPlayer($gameEvents, $this->players[0],
                StockType::LOW_RISK))->toEqual($amountOfStocks);


        // now sell part of the stocks
        $amountOfStocksToSell = $amountOfStocks / 2;
        $this->coreGameLogic->handle(
            $this->gameId,
            SellStocksForPlayer::create(
                $this->players[0],
                StockType::LOW_RISK,
                $amountOfStocksToSell
            )
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $currentStockPrice = StockPriceState::getCurrentStockPrice($gameEvents, StockType::LOW_RISK);
        expect(PlayerState::getGuthabenForPlayer($gameEvents,
            $this->players[0]))->toEqual($expectedGuthaben->add(new MoneyAmount($currentStockPrice->value * $amountOfStocksToSell)))
            ->and(PlayerState::getAmountOfAllStocksOfTypeForPlayer($gameEvents, $this->players[0],
                StockType::LOW_RISK))->toEqual($amountOfStocks - $amountOfStocksToSell);

        // sell rest of the stocks
        $this->coreGameLogic->handle(
            $this->gameId,
            SellStocksForPlayer::create(
                $this->players[0],
                StockType::LOW_RISK,
                $amountOfStocksToSell
            )
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getGuthabenForPlayer($gameEvents,
            $this->players[0]))->toEqual($expectedGuthaben->add(new MoneyAmount($currentStockPrice->value * $amountOfStocksToSell * 2)))
            ->and(PlayerState::getAmountOfAllStocksOfTypeForPlayer($gameEvents, $this->players[0],
                StockType::LOW_RISK))->toEqual(0);
    });

    it('throws exception if player tries to sell more stocks than she has', function () {
        $amountOfStocks = 100;

        /** @var TestCase $this */
        $this->coreGameLogic->handle(
            $this->gameId,
            BuyStocksForPlayer::create(
                $this->players[0],
                StockType::LOW_RISK,
                $amountOfStocks
            )
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getAmountOfAllStocksOfTypeForPlayer($gameEvents, $this->players[0],
            StockType::LOW_RISK))->toEqual($amountOfStocks)
            ->and(PlayerState::getAmountOfAllStocksOfTypeForPlayer($gameEvents, $this->players[0],
                StockType::HIGH_RISK))->toEqual(0);

        // now try to sell more stocks than the player has
        $amountOfStocksToSell = 150;
        $this->coreGameLogic->handle(
            $this->gameId,
            SellStocksForPlayer::create(
                $this->players[0],
                StockType::LOW_RISK,
                $amountOfStocksToSell
            )
        );
    })->throws(\RuntimeException::class, 'Du hast nicht genug Aktien', 1752753850);

    it('throws exception if player tries to sell wrong type of stocks', function () {
        $amountOfStocks = 100;

        /** @var TestCase $this */
        $this->coreGameLogic->handle(
            $this->gameId,
            BuyStocksForPlayer::create(
                $this->players[0],
                StockType::LOW_RISK,
                $amountOfStocks
            )
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getAmountOfAllStocksOfTypeForPlayer($gameEvents, $this->players[0],
            StockType::LOW_RISK))->toEqual($amountOfStocks)
            ->and(PlayerState::getAmountOfAllStocksOfTypeForPlayer($gameEvents, $this->players[0],
                StockType::HIGH_RISK))->toEqual(0);

        // now try to sell high risk stocks, but player only has low risk stocks
        $amountOfStocksToSell = 50;
        $this->coreGameLogic->handle(
            $this->gameId,
            SellStocksForPlayer::create(
                $this->players[0],
                StockType::HIGH_RISK,
                $amountOfStocksToSell
            )
        );
    })->throws(\RuntimeException::class, 'Du hast nicht genug Aktien', 1752753850);
});
