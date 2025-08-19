<?php
declare(strict_types=1);

use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\AcceptJobOffer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\CompleteMoneysheetForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EnterLebenshaltungskostenForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EnterSteuernUndAbgabenForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasCompletedMoneysheetForCurrentKonjunkturphase;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\JobRequirements;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Tests\TestCase;

beforeEach(function () {
    /** @var TestCase $this */
    $this->setupBasicGame();
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
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 3,
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
            new JobCardDefinition(
                id: new CardId('job1'),
                title: 'offered 1',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(100000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);
        $this->handle(
            ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE)
        );
        $this->handle(
            new EndSpielzug($this->players[0])
        );
        $this->handle(
            ActivateCard::create($this->players[1], CategoryId::BILDUNG_UND_KARRIERE)
        );
        $this->handle(
            new EndSpielzug($this->players[1])
        );

        $this->handle(AcceptJobOffer::create($this->getPlayers()[0], new CardId('job1')));
        $this->handle(
            new EndSpielzug($this->players[0])
        );

        $this->handle(
            EnterLebenshaltungskostenForPlayer::create($this->players[0],
                new MoneyAmount(35000))
        );
        $this->handle(
            EnterSteuernUndAbgabenForPlayer::create($this->players[0],
                new MoneyAmount(25000))
        );

        $this->handle(
            EnterLebenshaltungskostenForPlayer::create($this->players[1],
                new MoneyAmount(Configuration::LEBENSHALTUNGSKOSTEN_MIN_VALUE))
        );
        $initialBalanceForPlayer1 = PlayerState::getGuthabenForPlayer($this->getGameEvents(), $this->getPlayers()[0]);
        $initialBalanceForPlayer2 = PlayerState::getGuthabenForPlayer($this->getGameEvents(), $this->getPlayers()[1]);

        $this->handle(
            CompleteMoneysheetForPlayer::create($this->players[0])
        );
        $this->handle(
            CompleteMoneysheetForPlayer::create($this->players[1])
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect($gameEvents->findLastOrNullWhere(
                fn($event) => $event instanceof PlayerHasCompletedMoneysheetForCurrentKonjunkturphase
                    && $event->playerId->equals($this->players[0])
                    && $event->year->equals(KonjunkturphaseState::getCurrentYear($gameEvents))
            ) === null
        )->toBeFalse('Moneysheet should have been completed')
            ->and(PlayerState::getGuthabenForPlayer($this->getGameEvents(), $this->getPlayers()[0])->value)->toEqual($initialBalanceForPlayer1->value - 35000 - 25000 + 100000)
            ->and(PlayerState::getGuthabenForPlayer($this->getGameEvents(), $this->getPlayers()[1])->value)->toEqual($initialBalanceForPlayer2->value - Configuration::LEBENSHALTUNGSKOSTEN_MIN_VALUE);
    });

    it('throws an error if the player did not yet fill out the money sheet', function () {
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

    it('changes balance correctly when the player is insolvent', function () {
        /** @var TestCase $this */
        $this->setupInsolvenz();

        $cardsForTesting = [
            new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 3,
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
            new JobCardDefinition(
                id: new CardId('job1'),
                title: 'offered 1',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(100000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);
        $this->handle(
            ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE)
        );
        $this->handle(
            new EndSpielzug($this->players[0])
        );
        $this->handle(
            ActivateCard::create($this->players[1], CategoryId::BILDUNG_UND_KARRIERE)
        );
        $this->handle(
            new EndSpielzug($this->players[1])
        );

        $this->handle(AcceptJobOffer::create($this->getPlayers()[0], new CardId('job1')));
        $this->handle(
            new EndSpielzug($this->players[0])
        );

        $this->handle(
            EnterLebenshaltungskostenForPlayer::create($this->players[0],
                MoneySheetState::calculateLebenshaltungskostenForPlayer($this->getGameEvents(), $this->getPlayers()[0]))
        );
        $this->handle(
            EnterSteuernUndAbgabenForPlayer::create($this->players[0],
                new MoneyAmount(25000))
        );

        $this->handle(
            EnterLebenshaltungskostenForPlayer::create($this->players[1],
                MoneySheetState::calculateLebenshaltungskostenForPlayer($this->getGameEvents(), $this->getPlayers()[1]))
        );
        $initialBalanceForPlayer1 = PlayerState::getGuthabenForPlayer($this->getGameEvents(), $this->getPlayers()[0]);
        $initialBalanceForPlayer2 = PlayerState::getGuthabenForPlayer($this->getGameEvents(), $this->getPlayers()[1]);

        $this->handle(
            CompleteMoneysheetForPlayer::create($this->players[0])
        );
        $this->handle(
            CompleteMoneysheetForPlayer::create($this->players[1])
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect($gameEvents->findLastOrNullWhere(
                fn($event) => $event instanceof PlayerHasCompletedMoneysheetForCurrentKonjunkturphase
                    && $event->playerId->equals($this->players[0])
                    && $event->year->equals(KonjunkturphaseState::getCurrentYear($gameEvents))
            ) === null
        )->toBeFalse('Moneysheet should have been completed')
            ->and(PlayerState::getGuthabenForPlayer($this->getGameEvents(), $this->getPlayers()[0])->value)->toEqual($initialBalanceForPlayer1->value - Configuration::LEBENSHALTUNGSKOSTEN_MIN_VALUE + 10000)
            ->and(PlayerState::getGuthabenForPlayer($this->getGameEvents(), $this->getPlayers()[1])->value)->toEqual($initialBalanceForPlayer2->value - Configuration::LEBENSHALTUNGSKOSTEN_MIN_VALUE);
    });

    it('changes balance correctly when the player is insolvent and cannot pay Lebenshaltungskosten', function () {
        /** @var TestCase $this */
        $this->setupInsolvenz();

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
        $this->handle(
            ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE)
        );
        $this->handle(
            new EndSpielzug($this->players[0])
        );
        $this->handle(
            ActivateCard::create($this->players[1], CategoryId::BILDUNG_UND_KARRIERE)
        );
        $this->handle(
            new EndSpielzug($this->players[1])
        );

        $this->handle(
            EnterLebenshaltungskostenForPlayer::create($this->players[0],
                MoneySheetState::calculateLebenshaltungskostenForPlayer($this->getGameEvents(), $this->getPlayers()[0]))
        );

        $this->handle(
            EnterLebenshaltungskostenForPlayer::create($this->players[1],
                MoneySheetState::calculateLebenshaltungskostenForPlayer($this->getGameEvents(), $this->getPlayers()[1]))
        );
        $initialBalanceForPlayer1 = PlayerState::getGuthabenForPlayer($this->getGameEvents(), $this->getPlayers()[0]);
        $initialBalanceForPlayer2 = PlayerState::getGuthabenForPlayer($this->getGameEvents(), $this->getPlayers()[1]);

        $this->handle(
            CompleteMoneysheetForPlayer::create($this->players[0])
        );
        $this->handle(
            CompleteMoneysheetForPlayer::create($this->players[1])
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect($gameEvents->findLastOrNullWhere(
                fn($event) => $event instanceof PlayerHasCompletedMoneysheetForCurrentKonjunkturphase
                    && $event->playerId->equals($this->players[0])
                    && $event->year->equals(KonjunkturphaseState::getCurrentYear($gameEvents))
            ) === null
        )->toBeFalse('Moneysheet should have been completed')
            ->and(PlayerState::getGuthabenForPlayer($this->getGameEvents(), $this->getPlayers()[0])->value)->toEqual($initialBalanceForPlayer1->value)
            ->and(PlayerState::getGuthabenForPlayer($this->getGameEvents(), $this->getPlayers()[1])->value)->toEqual($initialBalanceForPlayer2->value - Configuration::LEBENSHALTUNGSKOSTEN_MIN_VALUE);
    });
});
