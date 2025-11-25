<?php
declare(strict_types=1);

namespace Tests\Feature\Livewire;

use Domain\CoreGameLogic\DrivingPorts\ForCoreGameLogic;
use Domain\Definitions\Insurance\ValueObject\InsuranceTypeEnum;
use Domain\Definitions\Investments\ValueObject\InvestmentId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

});



