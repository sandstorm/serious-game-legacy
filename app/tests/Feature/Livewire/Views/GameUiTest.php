<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\GameUi;
use App\Livewire\ValueObject\ExpensesTabEnum;
use Domain\CoreGameLogic\DrivingPorts\ForCoreGameLogic;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\BuyInvestmentsForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\DontSellInvestmentsForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SellInvestmentsForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\StartSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LebenshaltungskostenForPlayerWereEntered;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LoanWasTakenOutForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SteuernUndAbgabenForPlayerWereEntered;
use Domain\Definitions\Insurance\ValueObject\InsuranceTypeEnum;
use Domain\Definitions\Investments\ValueObject\InvestmentId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Assert;
use Tests\Feature\Livewire\Views\Helpers\GameUiTester;
use Tests\TestCase;

uses(RefreshDatabase::class);
beforeEach(function () {
    /** @var TestCase $this */
    $this->setupBasicGame();
    $this->app->instance(ForCoreGameLogic::class, $this->coreGameLogic);
});

describe('GameUi', function () {
    test('render GameUi', function () {
        /** @var TestCase $this */
        $gameUiTester = new GameUiTester($this, $this->getPlayers()[0], 'Player 0');
        $gameUiTester->testableGameUi->assertStatus(200);
        $gameUiTester
            ->startGame()
            ->startTurn()
            ->assertSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard();
    });

    test('play a card and finish turn', function (CategoryId $categoryId) {
        /** @var TestCase $this */
        $testCase = $this;

        new GameUiTester($testCase, $this->getPlayers()[0], 'Player 0')
            ->startGame()
            ->startTurn()
            ->assertSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard()
            ->drawAndPlayCard($categoryId)
            ->finishTurn()
            ->assertDoNotSeeMessage('Du musst erst einen Zeitstein für eine Aktion ausgeben')
            // check that player can not play a card after finishing turn
            ->tryToPlayCardWhenItIsNotThePlayersTurn($categoryId);

        // check that opponent player receives a message that it is their turn
        new GameUiTester($testCase, $this->getPlayers()[1], 'Player 1')
            ->startGame()
            ->startTurn()
            ->assertSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard();
    })->with([CategoryId::BILDUNG_UND_KARRIERE, CategoryId::SOZIALES_UND_FREIZEIT]);

    test('get enough Kompetenzsteine and accept a job offer', function () {
        /** @var TestCase $this */
        $testCase = $this;

        // first player plays first card for Bildung & Karriere and finishes turn
        new GameUiTester($testCase, $this->getPlayers()[0], 'Player 0')
            ->startGame()
            ->startTurn()
            ->assertSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard()
            ->drawAndPlayCard(CategoryId::BILDUNG_UND_KARRIERE)
            ->finishTurn()
            ->assertDoNotSeeMessage('Du musst erst einen Zeitstein für eine Aktion ausgeben');

        // second player uses 1 Zeitstein and finishes turn
        new GameUiTester($testCase, $this->getPlayers()[1], 'Player 1')
            ->startGame()
            ->startTurn()
            ->assertSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard()
            ->drawAndPlayCard(CategoryId::SOZIALES_UND_FREIZEIT)
            ->finishTurn()
            ->assertDoNotSeeMessage('Du musst erst einen Zeitstein für eine Aktion ausgeben');

        // first player plays second card for Bildung & Karriere and finishes turn
        new GameUiTester($testCase, $this->getPlayers()[0], 'Player 0')
            ->startTurn()
            ->assertSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard()
            ->drawAndPlayCard(CategoryId::BILDUNG_UND_KARRIERE)
            ->finishTurn()
            ->assertDoNotSeeMessage('Du musst erst einen Zeitstein für eine Aktion ausgeben');

        // second player uses 1 Zeitstein and finishes turn
        new GameUiTester($testCase, $this->getPlayers()[1], 'Player 1')
            ->startTurn()
            ->assertSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard()
            ->drawAndPlayCard(CategoryId::SOZIALES_UND_FREIZEIT)
            ->finishTurn()
            ->assertDoNotSeeMessage('Du musst erst einen Zeitstein für eine Aktion ausgeben');

        // first player accepts a job and finishes turn
        new GameUiTester($testCase, $this->getPlayers()[0], 'Player 0')
            ->startTurn()
            ->assertSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard()
            ->openJobBoard()
            ->acceptJobWhenPlayerCurrentlyHasNoJob()
            ->finishTurn()
            ->assertDoNotSeeMessage('Du musst erst einen Zeitstein für eine Aktion ausgeben');

        // check that opponent player receives a message that it is their turn
        new GameUiTester($testCase, $this->getPlayers()[1], 'Player 1')
            ->startTurn()
            ->assertSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard();
    });

    test('invest in Aktien', function () {
        /** @var TestCase $this */
        $testCase = $this;
        $stockId = InvestmentId::BETA_PEAR;
        $amount = 15;

        new GameUiTester($testCase, $this->getPlayers()[0], 'Player 0')
            ->startGame()
            ->startTurn()
            ->assertSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard()
            ->openInvestmentsOverview()
            ->chooseStocks()
            ->buyStocks($stockId, $amount);

        // opponent player has possibility to sell stocks
        new GameUiTester($testCase, $this->getPlayers()[1], 'Player 1')
            ->startGame()
            ->assertSidebarActionsAreVisible(false)
            ->seeUpdatedGameboard()
            ->sellStocksThatOtherPlayerIsBuying($stockId, 0);

        new GameUiTester($testCase, $this->getPlayers()[0], 'Player 0')
            ->finishTurn()
            ->assertDoNotSeeMessage('Du musst erst einen Zeitstein für eine Aktion ausgeben');

        // check that opponent player receives a message that it is their turn
        new GameUiTester($testCase, $this->getPlayers()[1], 'Player 1')
            ->startTurn()
            ->assertSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard();
    });

    test('do a Weiterbildung', function () {
        /** @var TestCase $this */
        $testCase = $this;

        new GameUiTester($testCase, $this->getPlayers()[0], 'Player 0')
            ->startGame()
            ->startTurn()
            ->assertSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard()
            ->doWeiterbildungWithSuccess()
            ->finishTurn()
            ->assertDoNotSeeMessage('Du musst erst einen Zeitstein für eine Aktion ausgeben');

        // check that opponent player receives a message that it is their turn
        new GameUiTester($testCase, $this->getPlayers()[1], 'Player 1')
            ->startGame()
            ->startTurn()
            ->assertSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard();
    });

    test('do a Minijob', function () {
        /** @var TestCase $this */
        $testCase = $this;

        new GameUiTester($testCase, $this->getPlayers()[0], 'Player 0')
            ->startGame()
            ->startTurn()
            ->assertSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard()
            ->doMinijob()
            ->finishTurn()
            ->assertDoNotSeeMessage('Du musst erst einen Zeitstein für eine Aktion ausgeben');

        // check that opponent player receives a message that it is their turn
        new GameUiTester($testCase, $this->getPlayers()[1], 'Player 1')
            ->startGame()
            ->startTurn()
            ->assertSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard();
    });

    test('take out an insurance', function () {
        /** @var TestCase $this */
        $testCase = $this;
        $insurancesToChange = [
            ['type' => InsuranceTypeEnum::HAFTPFLICHT, 'changeTo' => true],
            ['type' => InsuranceTypeEnum::BERUFSUNFAEHIGKEITSVERSICHERUNG, 'changeTo' => true]
        ];

        $gameUiTester = new GameUiTester($testCase, $this->getPlayers()[0], 'Player 0');

        $availableZeitsteine = $gameUiTester->getAvailableZeitsteine();
        $playersZeitsteineBeforeAction = $gameUiTester->getPlayersZeitsteine();
        $availableCategorySlots = $gameUiTester->getAvailableCategorySlots($gameUiTester->categoryIds);
        $usedCategorySlotsBeforeAction = $gameUiTester->getOccupiedCategorySlots($gameUiTester->categoryIds);
        $availableKompetenzSlots = $gameUiTester->getAvailableKompetenzSlots();
        $playersKompetenzsteineBeforeAction = $gameUiTester->getPlayersKompetenzsteine();

        $gameUiTester
            ->startGame()
            ->startTurn()
            ->assertSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard()
            // check that Zeitsteine are rendered correctly
            ->assertVisibilityOfZeitsteine($playersZeitsteineBeforeAction, $availableZeitsteine)
            // check that Zeitsteinslots are rendered correctly
            ->assertVisibilityOfCategorySlots($availableCategorySlots, $usedCategorySlotsBeforeAction)
            // check that Kompetenzen are rendered correctly
            ->assertVisibilityOfKompetenzen($playersKompetenzsteineBeforeAction, $availableKompetenzSlots)
            ->openMoneySheetInsurance()
            ->assertSeeMoneySheetInsurance()
            ->assertSeeAnnualInsurancesCost()
            ->changeInsurance($insurancesToChange)
            ->confirmInsuranceChoice()
            ->assertSeeAnnualInsurancesCost()
            ->assertSeeInsuranceChangeInEreignisprotokoll($insurancesToChange)
            ->closeMoneySheet();

        $playersZeitsteineAfterAction = $gameUiTester->getPlayersZeitsteine();
        $usedCategorySlotsAfterAction = $gameUiTester->getOccupiedCategorySlots($gameUiTester->categoryIds);
        $playersKompetenzsteineAfterAction = $gameUiTester->getPlayersKompetenzsteine();

        $gameUiTester
            ->assertVisibilityOfZeitsteine($playersZeitsteineAfterAction, $availableZeitsteine)
            ->assertVisibilityOfCategorySlots($availableCategorySlots, $usedCategorySlotsAfterAction)
            ->compareUsedSlots(null, $usedCategorySlotsBeforeAction, $usedCategorySlotsAfterAction)
            ->assertVisibilityOfKompetenzen($playersKompetenzsteineAfterAction, $availableKompetenzSlots);

        Assert::assertEquals(
            $playersZeitsteineBeforeAction,
            $playersZeitsteineAfterAction,
            'Amount of Zeitsteine has not changed'
        );

        foreach ($playersKompetenzsteineAfterAction as $categoryName => $amount) {
            Assert::assertEquals(
                $playersKompetenzsteineAfterAction[$categoryName],
                $playersKompetenzsteineBeforeAction[$categoryName],
                'Kompetenzsteine have not changed'
            );
        }
    });

    test('show Lebensziel of current player', function () {
        /** @var TestCase $this */
        $testCase = $this;

        new GameUiTester($testCase, $this->getPlayers()[0], 'Player 0')
            ->startGame()
            ->startTurn()
            ->assertSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard()
            ->openLebenszielModal()
            ->assertSeeLebenszielModal();
    });

    test('try to finish turn without using a Zeitstein, close error message, play a card and finish turn', function () {
        /** @var TestCase $this */
        $testCase = $this;

        new GameUiTester($testCase, $this->getPlayers()[0], 'Player 0')
            ->startGame()
            ->startTurn()
            ->assertSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard()
            ->finishTurn()
            ->assertSeeMessage('Du musst erst einen Zeitstein für eine Aktion ausgeben')
            ->closeMessage()
            ->drawAndPlayCard(CategoryId::BILDUNG_UND_KARRIERE)
            ->finishTurn()
            ->assertDoNotSeeMessage('Du musst erst einen Zeitstein für eine Aktion ausgeben');

        // check that opponent player receives a message that it is their turn
        new GameUiTester($testCase, $this->getPlayers()[1], 'Player 1')
            ->startGame()
            ->startTurn()
            ->assertSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard();
    });

    // Regression coverage for issue #652 — popups not behaving correctly
    describe('popups (issue #652)', function () {
        test('sell modal stays visible for player B across a passive WebSocket-style re-render after player A invests', function () {
            /** @var TestCase $this */
            $testCase = $this;
            $stockId = InvestmentId::BETA_PEAR;

            new GameUiTester($testCase, $this->getPlayers()[0], 'Player 0')
                ->startGame()
                ->startTurn()
                ->openInvestmentsOverview()
                ->chooseStocks()
                ->buyStocks($stockId, 15);

            // Player B advances past the konjunkturphase-start screen, then sees the sell-popup.
            $playerBComponent = Livewire::test(GameUi::class, [
                'gameId' => $this->getGameId(),
                'myself' => $this->getPlayers()[1],
            ])
                ->call('startKonjunkturphaseForPlayer');

            $playerBComponent
                ->assertSet('sellInvestmentsModalIsVisible', true)
                ->assertSeeHtml("hat in $stockId->value investiert!");

            // Simulate a WebSocket-triggered re-render with no local user action. Before the fix,
            // an empty notifyGameStateUpdated() relied on cached gameEvents and could leave the
            // flag flipped to false.
            $playerBComponent
                ->call('notifyGameStateUpdated')
                ->assertSet('sellInvestmentsModalIsVisible', true)
                ->assertSeeHtml("hat in $stockId->value investiert!");
        });

        test('sell modal can be dismissed when triggered by a PlayerHasSoldInvestment event', function () {
            /** @var TestCase $this */
            $stockId = InvestmentId::BETA_PEAR;
            $player0 = $this->getPlayers()[0];
            $player1 = $this->getPlayers()[1];

            // Turn 1 for Player 0: start turn, buy stocks, then Player 1 declines to sell, Player 0 ends turn
            $this->handle(new StartSpielzug($player0));
            $this->handle(BuyInvestmentsForPlayer::create($player0, $stockId, 5));
            $this->handle(DontSellInvestmentsForPlayer::create($player1, $stockId));
            $this->handle(new EndSpielzug($player0));

            // Turn 1 for Player 1: start turn, use a Zeitstein, end turn
            $this->handle(new StartSpielzug($player1));
            $this->handle(ActivateCard::create($player1, CategoryId::BILDUNG_UND_KARRIERE));
            $this->handle(new EndSpielzug($player1));

            // Turn 2 for Player 0: start turn and SELL stocks — triggers PlayerHasSoldInvestment
            $this->handle(new StartSpielzug($player0));
            $this->handle(SellInvestmentsForPlayer::create($player0, $stockId, 5));

            // Player 1's component should show the sell modal (triggered by sell, not buy).
            // Before Fix 1, closeSellInvestmentsModal() would call findLast(PlayerHasBoughtInvestment)
            // which would find a stale event from turn 1 and issue a DontSell for the wrong turn,
            // or potentially throw if no buy event existed at all.
            $playerBComponent = Livewire::test(GameUi::class, [
                'gameId' => $this->getGameId(),
                'myself' => $player1,
            ])->call('startKonjunkturphaseForPlayer');

            $playerBComponent
                ->assertSet('sellInvestmentsModalIsVisible', true)
                ->assertSeeHtml("hat in $stockId->value investiert!");

            $playerBComponent
                ->call('closeSellInvestmentsModal')
                ->assertSet('sellInvestmentsModalIsVisible', false);
        });
    });

    describe('take out a loan', function () {
        test('a loan above the credit limit is rejected with an error at the input field', function () {
            /** @var TestCase $this */
            new GameUiTester($this, $this->getPlayers()[0], 'Player 0')
                ->startGame()
                ->startTurn()
                ->testableGameUi
                ->call('showTakeOutALoan')
                ->set('takeOutALoanForm.loanAmount', '1000000')
                ->call('takeOutALoan')
                ->assertHasErrors(['takeOutALoanForm.loanAmount' => 'Du kannst keinen Kredit aufnehmen, der höher ist als das Kreditlimit.'])
                // the error is rendered at the input field of the (still open) form
                ->assertSet('takeOutALoanIsVisible', true)
                ->assertSeeHtml('<span class="form-error">Du kannst keinen Kredit aufnehmen, der höher ist als das Kreditlimit.</span>');

            expect($this->getGameEvents()->findLastOrNull(LoanWasTakenOutForPlayer::class))->toBeNull();
        });

        test('a loan above the credit limit is rejected even if the client tampers with the form values', function () {
            /** @var TestCase $this */
            new GameUiTester($this, $this->getPlayers()[0], 'Player 0')
                ->startGame()
                ->startTurn()
                ->testableGameUi
                ->call('showTakeOutALoan')
                // a manipulated request could send arbitrary values for any form property
                ->set('takeOutALoanForm.sumOfAllAssets', 1_000_000_000)
                ->set('takeOutALoanForm.salary', 1_000_000_000)
                ->set('takeOutALoanForm.loanAmount', '1000000')
                ->call('takeOutALoan')
                ->assertHasErrors(['takeOutALoanForm.loanAmount' => 'Du kannst keinen Kredit aufnehmen, der höher ist als das Kreditlimit.'])
                // the error is rendered at the input field of the (still open) form
                ->assertSet('takeOutALoanIsVisible', true)
                ->assertSeeHtml('<span class="form-error">Du kannst keinen Kredit aufnehmen, der höher ist als das Kreditlimit.</span>');

            expect($this->getGameEvents()->findLastOrNull(LoanWasTakenOutForPlayer::class))->toBeNull();
        });
    });

    describe('numeric inputs while other players are playing (issue #680)', function () {
        // The browser always sends input values as strings. If the server returns them with a different type
        // (e.g. 1125 instead of "1125"), Livewire overwrites the input field on every re-render, e.g. when another
        // player ends their turn. Everything typed during that request is lost.
        test('a re-render returns the input value unchanged', function (string $property) {
            /** @var TestCase $this */
            $testableGameUi = new GameUiTester($this, $this->getPlayers()[0], 'Player 0')
                ->startGame()
                ->startTurn()
                ->testableGameUi
                ->set($property, '1125')
                ->call('notifyGameStateUpdated');

            expect($testableGameUi->get($property))->toBe('1125');
        })->with([
            'moneySheetSteuernUndAbgabenForm.steuernUndAbgaben',
            'moneySheetLebenshaltungskostenForm.lebenshaltungskosten',
            'takeOutALoanForm.loanAmount',
            'buyInvestmentsForm.amount',
            'sellInvestmentsForm.amount',
        ]);

        test('an invalid Steuern und Abgaben input is rejected without counting as a try', function (string $input) {
            /** @var TestCase $this */
            new GameUiTester($this, $this->getPlayers()[0], 'Player 0')
                ->startGame()
                ->startTurn()
                ->testableGameUi
                ->call('showExpensesTab', ExpensesTabEnum::TAXES->value)
                ->set('moneySheetSteuernUndAbgabenForm.steuernUndAbgaben', $input)
                ->call('setSteuernUndAbgaben')
                ->assertHasErrors(['moneySheetSteuernUndAbgabenForm.steuernUndAbgaben']);

            expect($this->getGameEvents()->findLastOrNull(SteuernUndAbgabenForPlayerWereEntered::class))->toBeNull();
        })->with(['', 'abc', '1e3', '12.345', '-1']);

        test('an invalid Lebenshaltungskosten input is rejected without counting as a try', function (string $input) {
            /** @var TestCase $this */
            new GameUiTester($this, $this->getPlayers()[0], 'Player 0')
                ->startGame()
                ->startTurn()
                ->testableGameUi
                ->call('showExpensesTab', ExpensesTabEnum::LIVING_COSTS->value)
                ->set('moneySheetLebenshaltungskostenForm.lebenshaltungskosten', $input)
                ->call('setLebenshaltungskosten')
                ->assertHasErrors(['moneySheetLebenshaltungskostenForm.lebenshaltungskosten']);

            expect($this->getGameEvents()->findLastOrNull(LebenshaltungskostenForPlayerWereEntered::class))->toBeNull();
        })->with(['', 'abc', '1e3', '12.345', '-1']);

        test('a decimal Steuern und Abgaben input is accepted', function () {
            /** @var TestCase $this */
            new GameUiTester($this, $this->getPlayers()[0], 'Player 0')
                ->startGame()
                ->startTurn()
                ->testableGameUi
                ->call('showExpensesTab', ExpensesTabEnum::TAXES->value)
                ->set('moneySheetSteuernUndAbgabenForm.steuernUndAbgaben', '1250.50')
                ->call('setSteuernUndAbgaben');

            $event = $this->getGameEvents()->findLast(SteuernUndAbgabenForPlayerWereEntered::class);
            expect($event->getPlayerInput()->value)->toBe(1250.5);
        });
    });
});
