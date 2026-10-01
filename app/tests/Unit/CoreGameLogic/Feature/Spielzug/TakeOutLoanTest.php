<?php

declare(strict_types=1);

use App\Livewire\Forms\TakeOutALoanForm;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\BuyInvestmentsForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\RepayLoanForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\StartSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\TakeOutALoanForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LoanWasRepaidForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LoanWasTakenOutForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;
use Domain\Definitions\Investments\ValueObject\InvestmentId;
use Tests\ComponentWithForm;
use Tests\TestCase;

beforeEach(function () {
    /** @var TestCase $this */
    $this->setupBasicGame();
});

describe('handleTakeOutALoanForPlayer', function () {

    it('throws an exception when the player is insolvent', function () {
        /** @var TestCase $this */
        $this->setupInsolvenz();

        expect(PlayerState::isPlayerInsolvent($this->getGameEvents(), $this->players[0]))->toBeTrue("Player should be insolvent");

        $takeoutLoanFormComponent = new ComponentWithForm();
        $takeoutLoanFormComponent->mount(TakeOutALoanForm::class);

        /** @var TakeOutALoanForm $takeoutLoanForm */
        $takeoutLoanForm = $takeoutLoanFormComponent->form;
        $takeoutLoanForm->loanAmount = 10000;
        $takeoutLoanForm->zinssatz = 4;

        // player 0 takes out a loan
        $this->coreGameLogic->handle($this->gameId, TakeOutALoanForPlayer::create(
            $this->players[0],
            $takeoutLoanForm->loanAmount
        ));

    })->throws(\RuntimeException::class, "Cannot take out a loan: Du bist insolvent.", 1756200359);

    it('throws an exception when the loan amount exceeds the credit limit of the player', function () {
        /** @var TestCase $this */
        // player has no job and only the Startkapital -> credit limit is 80% of the Startkapital
        $this->coreGameLogic->handle($this->gameId, TakeOutALoanForPlayer::create(
            $this->players[0],
            1_000_000
        ));
    })->throws(\RuntimeException::class, 'Cannot take out a loan: Du kannst keinen Kredit aufnehmen, der höher ist als das Kreditlimit.', 1756200359);

    it('throws an exception when the loan amount is not positive', function () {
        /** @var TestCase $this */
        $this->coreGameLogic->handle($this->gameId, TakeOutALoanForPlayer::create(
            $this->players[0],
            0
        ));
    })->throws(\RuntimeException::class, 'Cannot take out a loan: Der Kreditbetrag muss größer als 0 sein.', 1756200359);

    it('adds the loan amount to the player\s Guthaben', function () {
        /** @var TestCase $this */

        $takeoutLoanFormComponent = new ComponentWithForm();
        $takeoutLoanFormComponent->mount(TakeOutALoanForm::class);

        $loanAmount = 10000;

        /** @var TakeOutALoanForm $takeoutLoanForm */
        $takeoutLoanForm = $takeoutLoanFormComponent->form;
        $takeoutLoanForm->loanAmount = $loanAmount;
        $takeoutLoanForm->zinssatz = 4;

        // player 0 takes out a loan
        $this->coreGameLogic->handle($this->gameId, TakeOutALoanForPlayer::create(
            $this->players[0],
            $takeoutLoanForm->loanAmount
        ));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var LoanWasTakenOutForPlayer $loanWasTakenOut */
        $loanWasTakenOut = $gameEvents->findLast(LoanWasTakenOutForPlayer::class);
        expect($loanWasTakenOut->getResourceChanges($this->players[0])->guthabenChange)->toEqual(new MoneyAmount(10000));
    });
});

describe('handleRepayLoanForPlayer', function () {
    it('correctly repays loans', function () {
        /** @var TestCase $this */

        // first player needs to take out a loan
        $takeoutLoanFormComponent = new ComponentWithForm();
        $takeoutLoanFormComponent->mount(TakeOutALoanForm::class);

        $loanAmount = 10000;

        /** @var TakeOutALoanForm $takeoutLoanForm */
        $takeoutLoanForm = $takeoutLoanFormComponent->form;
        $takeoutLoanForm->loanAmount = $loanAmount;
        $takeoutLoanForm->zinssatz = 4;

        // player 0 takes out a loan
        $this->coreGameLogic->handle($this->gameId, TakeOutALoanForPlayer::create(
            $this->players[0],
            $takeoutLoanForm->loanAmount
        ));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var LoanWasTakenOutForPlayer $loanWasTakenOut */
        $loanWasTakenOut = $gameEvents->findLast(LoanWasTakenOutForPlayer::class);
        expect($loanWasTakenOut->getResourceChanges($this->players[0])->guthabenChange)->toEqual(new MoneyAmount(10000))
            ->and(PlayerState::getGuthabenForPlayer(
                $gameEvents,
                $this->players[0]
            ))->toEqual(new MoneyAmount(Configuration::STARTKAPITAL_VALUE + $loanAmount));
        $loanId = $loanWasTakenOut->loanId;

        // player 0 repays the loan
        $this->coreGameLogic->handle($this->gameId, RepayLoanForPlayer::create(
            $this->players[0],
            $loanId
        ));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        $expectedRepaymentCost = new MoneyAmount(-12120);

        /** @var LoanWasRepaidForPlayer $loanWasRepaid */
        $loanWasRepaid = $gameEvents->findLast(LoanWasRepaidForPlayer::class);
        expect($loanWasRepaid->getResourceChanges($this->players[0])->guthabenChange)->toEqual($expectedRepaymentCost)
            ->and(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]))
            ->toEqual(new MoneyAmount(Configuration::STARTKAPITAL_VALUE + $loanAmount + $expectedRepaymentCost->value))
            ->and(MoneySheetState::getOpenRatesForLoan($gameEvents, $this->players[0], $loanId))->toEqual(0)
            ->and(MoneySheetState::getOpenRepaymentValueForLoan($gameEvents, $this->players[0], $loanId))->toEqual(new MoneyAmount(0))
            ->and(MoneySheetState::getAnnualExpensesForAllLoans($gameEvents, $this->players[0]))->toEqual(new MoneyAmount(0))
            ->and(MoneySheetState::getTotalOpenRepaymentValueForAllLoans($gameEvents, $this->players[0]))->toEqual(new MoneyAmount(0));
    });

    it('throws exception when player does not have enough money to pay back the loan', function () {
        /** @var TestCase $this */

        // first player needs to take out a loan
        $takeoutLoanFormComponent = new ComponentWithForm();
        $takeoutLoanFormComponent->mount(TakeOutALoanForm::class);

        $loanAmount = 10000;

        /** @var TakeOutALoanForm $takeoutLoanForm */
        $takeoutLoanForm = $takeoutLoanFormComponent->form;
        $takeoutLoanForm->loanAmount = $loanAmount;
        $takeoutLoanForm->zinssatz = 5;

        // player 0 takes out a loan
        $this->coreGameLogic->handle($this->gameId, TakeOutALoanForPlayer::create(
            $this->players[0],
            $takeoutLoanForm->loanAmount
        ));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var LoanWasTakenOutForPlayer $loanWasTakenOut */
        $loanWasTakenOut = $gameEvents->findLast(LoanWasTakenOutForPlayer::class);
        expect($loanWasTakenOut->getResourceChanges($this->players[0])->guthabenChange)->toEqual(new MoneyAmount(10000))
            ->and(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]))
            ->toEqual(new MoneyAmount(Configuration::STARTKAPITAL_VALUE + $loanAmount));
        $loanId = $loanWasTakenOut->loanId;

        // buy investments to reduce guthaben
        $amount = intval(Configuration::STARTKAPITAL_VALUE / Configuration::INITIAL_INVESTMENT_PRICE);
        $this->handle(new StartSpielzug($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, BuyInvestmentsForPlayer::create(
            $this->players[0],
            InvestmentId::MERFEDES_PENZ,
            $amount
        ));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]))
            ->toEqual(new MoneyAmount(10000));

        // player 0 repays the loan but does not have enough money
        $this->coreGameLogic->handle($this->gameId, RepayLoanForPlayer::create(
            $this->players[0],
            $loanId
        ));
    })->throws(RuntimeException::class, 'Du hast nicht genug Ressourcen', 1756813341);
});
