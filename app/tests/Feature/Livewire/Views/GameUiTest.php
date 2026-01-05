<?php

namespace Tests\Feature\Livewire;

use Domain\CoreGameLogic\DrivingPorts\ForCoreGameLogic;
use Domain\Definitions\Insurance\ValueObject\InsuranceTypeEnum;
use Domain\Definitions\Investments\ValueObject\InvestmentId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Livewire\Views\Helpers\GameUiTester;
use Tests\TestCase;

uses(RefreshDatabase::class);
beforeEach(function () {
    /** @var TestCase $this */
    $this->setupBasicGame();
    $this->app->instance(ForCoreGameLogic::class, $this->coreGameLogic);
});

describe('GameUi', function () {
    it('renders GameUi', function () {
        /** @var TestCase $this */
        $gameUiTester = new GameUiTester($this, $this->players[0], 'Player 0');
        $gameUiTester->testableGameUi->assertStatus(200);
        $gameUiTester
            ->startGame()
            ->checkThatSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard();
    });

    it('plays a card and finishes turn', function (CategoryId $categoryId) {
        /** @var TestCase $this */
        $testCase = $this;

        new GameUiTester($testCase, $this->players[0], 'Player 0')
            ->startGame()
            ->checkThatSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard()
            ->drawAndPlayCard($categoryId)
            ->finishTurn()
            ->assertDoNotSeeMessage('Du musst erst einen Zeitstein für eine Aktion ausgeben')
            // check that player can not play a card after finishing turn
            ->tryToPlayCardWhenItIsNotThePlayersTurn($categoryId);

        // check that opponent player receives a message that it is their turn
        new GameUiTester($testCase, $this->players[1], 'Player 1')
            ->startGame()
            ->startTurn()
            ->checkThatSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard();

    })->with([CategoryId::BILDUNG_UND_KARRIERE, CategoryId::SOZIALES_UND_FREIZEIT]);

    it('gets enough Kompetenzsteine and accepts a job offer', function () {
        /** @var TestCase $this */
        $testCase = $this;

        // first player plays first card for Bildung & Karriere and finishes turn
        new GameUiTester($testCase, $this->players[0], 'Player 0')
            ->startGame()
            ->checkThatSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard()
            ->drawAndPlayCard(CategoryId::BILDUNG_UND_KARRIERE)
            ->finishTurn()
            ->assertDoNotSeeMessage('Du musst erst einen Zeitstein für eine Aktion ausgeben');

        // second player uses 1 Zeitstein and finishes turn
        new GameUiTester($testCase, $this->players[1], 'Player 1')
            ->startGame()
            ->startTurn()
            ->checkThatSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard()
            ->drawAndPlayCard(CategoryId::SOZIALES_UND_FREIZEIT)
            ->finishTurn()
            ->assertDoNotSeeMessage('Du musst erst einen Zeitstein für eine Aktion ausgeben');

        // first player plays second card for Bildung & Karriere and finishes turn
        new GameUiTester($testCase, $this->players[0], 'Player 0')
            ->startTurn()
            ->checkThatSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard()
            ->drawAndPlayCard(CategoryId::BILDUNG_UND_KARRIERE)
            ->finishTurn()
            ->assertDoNotSeeMessage('Du musst erst einen Zeitstein für eine Aktion ausgeben');

        // second player uses 1 Zeitstein and finishes turn
        new GameUiTester($testCase, $this->players[1], 'Player 1')
            ->startTurn()
            ->checkThatSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard()
            ->drawAndPlayCard(CategoryId::SOZIALES_UND_FREIZEIT)
            ->finishTurn()
            ->assertDoNotSeeMessage('Du musst erst einen Zeitstein für eine Aktion ausgeben');

        // first player accepts a job and finishes turn
        new GameUiTester($testCase, $this->players[0], 'Player 0')
            ->startTurn()
            ->checkThatSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard()
            ->openJobBoard()
            ->acceptJobWhenPlayerCurrentlyHasNoJob()
            ->finishTurn()
            ->assertDoNotSeeMessage('Du musst erst einen Zeitstein für eine Aktion ausgeben');

        // check that opponent player receives a message that it is their turn
        new GameUiTester($testCase, $this->players[1], 'Player 1')
            ->startTurn()
            ->checkThatSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard();
    });

    it('invests in Aktien', function () {
        /** @var TestCase $this */
        $testCase = $this;
        $stockId = InvestmentId::BETA_PEAR;
        $amount = 15;

        new GameUiTester($testCase, $this->players[0], 'Player 0')
            ->startGame()
            ->checkThatSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard()
            ->openInvestmentsOverview()
            ->chooseStocks()
            ->buyStocks($stockId, $amount);

        // opponent player has possibility to sell stocks
        new GameUiTester($testCase, $this->players[1], 'Player 1')
            ->startGame()
            ->checkThatSidebarActionsAreVisible(false)
            ->seeUpdatedGameboard()
            ->sellStocksThatOtherPlayerIsBuying($stockId);

        new GameUiTester($testCase, $this->players[0], 'Player 0')
            ->finishTurn()
            ->assertDoNotSeeMessage('Du musst erst einen Zeitstein für eine Aktion ausgeben');

        // check that opponent player receives a message that it is their turn
        new GameUiTester($testCase, $this->players[1], 'Player 1')
            ->startTurn()
            ->checkThatSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard();
    });

    it('does a Weiterbildung', function () {
        /** @var TestCase $this */
        $testCase = $this;

        new GameUiTester($testCase, $this->players[0], 'Player 0')
            ->startGame()
            ->checkThatSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard()
            ->doWeiterbildungWithSuccess()
            ->finishTurn()
            ->assertDoNotSeeMessage('Du musst erst einen Zeitstein für eine Aktion ausgeben');

        // check that opponent player receives a message that it is their turn
        new GameUiTester($testCase, $this->players[1], 'Player 1')
            ->startGame()
            ->startTurn()
            ->checkThatSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard();
    });

    it('does a Minijob', function () {
        /** @var TestCase $this */
        $testCase = $this;

        new GameUiTester($testCase, $this->players[0], 'Player 0')
            ->startGame()
            ->checkThatSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard()
            ->doMinijob()
            ->finishTurn()
            ->assertDoNotSeeMessage('Du musst erst einen Zeitstein für eine Aktion ausgeben');

        // check that opponent player receives a message that it is their turn
        new GameUiTester($testCase, $this->players[1], 'Player 1')
            ->startGame()
            ->startTurn()
            ->checkThatSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard();
    });

    it('takes out an insurance', function () {
        /** @var TestCase $this */
        $testCase = $this;
        $insurancesToChange = [
            ['type' => InsuranceTypeEnum::HAFTPFLICHT, 'changeTo' => true],
            ['type' => InsuranceTypeEnum::BERUFSUNFAEHIGKEITSVERSICHERUNG, 'changeTo' => true]
        ];

        // ToDo: Vergleich Zeitsteine vor und nach Aktion
        // ToDo: Vergleich Kompetenzen vor und nach Aktion
        // ToDo: Vergleich Zeitsteinslots vor und nach Aktion

        new GameUiTester($testCase, $this->players[0], 'Player 0')
            ->startGame()
            ->checkThatSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard()
            ->openMoneySheetInsurance()
            ->seeMoneySheetInsurance()
            ->seeAnnualInsurancesCost()
            ->changeInsurance($insurancesToChange)
            ->confirmInsuranceChoice()
            ->seeAnnualInsurancesCost()
            ->seeInsuranceChangeInEreignisprotokoll($insurancesToChange)
            ->closeMoneySheet();
    });

    it('shows Lebensziel of current player', function () {
        /** @var TestCase $this */
        $testCase = $this;

        new GameUiTester($testCase, $this->players[0], 'Player 0')
            ->startGame()
            ->checkThatSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard()
            ->openLebenszielModal()
            ->seeLebenszielModal();
    });

    it('tries to finish turn without using a Zeitstein, closes error message, plays a card and finishes turn', function () {
        /** @var TestCase $this */
        $testCase = $this;

        new GameUiTester($testCase, $this->players[0], 'Player 0')
            ->startGame()
            ->checkThatSidebarActionsAreVisible(true)
            ->seeUpdatedGameboard()
            ->finishTurn()
            ->assertSeeMessage('Du musst erst einen Zeitstein für eine Aktion ausgeben')
            ->closeMessage()
            ->drawAndPlayCard(CategoryId::BILDUNG_UND_KARRIERE)
            ->finishTurn()
            ->assertDoNotSeeMessage('Du musst erst einen Zeitstein für eine Aktion ausgeben');
    });

});


/*

///////////////////////////////////////////////////////////////////////////

    // check that player can not draw card
    ->call('activateCard', 'Bildung & Karriere')
    ->assertSee('Du hast bereits eine andere Aktion ausgeführt')
    ->call('activateCard', 'Freizeit & Soziales')
    ->assertSee('Du hast bereits eine andere Aktion ausgeführt')
    // check that player can not do a Weiterbildung
    ->call('showWeiterbildung')
    ->assertSee('Du kannst nur eine Zeitsteinaktion pro Runde ausführen')
    // check that player can not do another Minijob
    ->call('doMinijob')
    ->assertSee('Du kannst nur eine Zeitsteinaktion pro Runde ausführen')

///////////////////////////////////////////////////////////////////////////

*/

