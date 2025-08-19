<?php
declare(strict_types=1);

use App\Livewire\Forms\TakeOutALoanForm;

use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Moneysheet\ValueObject\LoanId;
use Domain\CoreGameLogic\Feature\Spielzug\Command\BuyInvestmentsForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\RepayLoanForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\TakeOutALoanForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\LoanData;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LoanForPlayerWasCorrected;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LoanForPlayerWasEntered;
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
    it('player gets fine for entering loan data wrong', function () {
        $takeoutLoanFormComponent = new ComponentWithForm();
        $takeoutLoanFormComponent->mount(TakeOutALoanForm::class);

        /** @var TakeOutALoanForm $takeoutLoanForm */
        $takeoutLoanForm = $takeoutLoanFormComponent->form;
        $takeoutLoanForm->loanAmount = 10000;
        $takeoutLoanForm->totalRepayment = 12500;
        $takeoutLoanForm->repaymentPerKonjunkturphase = 600; // wrong value
        $takeoutLoanForm->sumOfAllAssets = Configuration::STARTKAPITAL_VALUE;
        $takeoutLoanForm->zinssatz = 5;
        $takeoutLoanForm->loanId = LoanId::unique()->value;
        $takeoutLoanForm->wasInsolvent = false;

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
            ->and($loanWasCorrectedEvent->getResourceChanges($this->players[0])->guthabenChange)->toEqual(new MoneyAmount(-Configuration::FINE_VALUE))
            ->and(PlayerState::getGuthabenForPlayer($gameEvents,
                $this->players[0]))->toEqual(new MoneyAmount(Configuration::STARTKAPITAL_VALUE - Configuration::FINE_VALUE));
    });

    it('throws an exception when the player is insolvent', function () {
        /** @var TestCase $this */
        $this->setupInsolvenz();

        expect(PlayerState::isPlayerInsolvent($this->getGameEvents(), $this->players[0]))->toBeTrue("Player should be insolvent");

        $takeoutLoanFormComponent = new ComponentWithForm();
        $takeoutLoanFormComponent->mount(TakeOutALoanForm::class);

        /** @var TakeOutALoanForm $takeoutLoanForm */
        $takeoutLoanForm = $takeoutLoanFormComponent->form;
        $takeoutLoanForm->loanAmount = 10000;
        $takeoutLoanForm->totalRepayment = 12500;
        $takeoutLoanForm->repaymentPerKonjunkturphase = 625;
        $takeoutLoanForm->guthaben = Configuration::STARTKAPITAL_VALUE;
        $takeoutLoanForm->zinssatz = 5;
        $takeoutLoanForm->loanId = LoanId::unique()->value;

        // player 0 takes out a loan
        $this->coreGameLogic->handle($this->gameId, TakeOutALoanForPlayer::create(
            $this->players[0],
            $takeoutLoanForm
        ));

    })->throws(\RuntimeException::class, "Cannot take out a loan: Du bist insolvent", 1756200359);

    it('adds the loan amount to the player\s Guthaben', function () {
        /** @var TestCase $this */

        $takeoutLoanFormComponent = new ComponentWithForm();
        $takeoutLoanFormComponent->mount(TakeOutALoanForm::class);

        $loanAmount = 10000;

        /** @var TakeOutALoanForm $takeoutLoanForm */
        $takeoutLoanForm = $takeoutLoanFormComponent->form;
        $takeoutLoanForm->loanAmount = $loanAmount;
        $takeoutLoanForm->totalRepayment = 12500;
        $takeoutLoanForm->repaymentPerKonjunkturphase = 625; // correct value
        $takeoutLoanForm->sumOfAllAssets = Configuration::STARTKAPITAL_VALUE;
        $takeoutLoanForm->zinssatz = 5;
        $takeoutLoanForm->loanId = LoanId::unique()->value;

        // player 0 takes out a loan
        $this->coreGameLogic->handle($this->gameId, TakeOutALoanForPlayer::create(
            $this->players[0],
            $takeoutLoanForm
        ));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var LoanForPlayerWasEntered $loanWasEntered */
        $loanWasEntered = $gameEvents->findLast(LoanForPlayerWasEntered::class);

        expect($loanWasEntered)->toBeInstanceOf(LoanForPlayerWasEntered::class)
            ->and($loanWasEntered->getLoanData())->toEqual(new LoanData(
                loanAmount: new MoneyAmount(10000),
                totalRepayment: new MoneyAmount(12500),
                repaymentPerKonjunkturphase: new MoneyAmount(625),
            ))
            ->and($loanWasEntered->wasInputCorrect())->toBeTrue()
            ->and(PlayerState::getGuthabenForPlayer($gameEvents,
                $this->players[0]))->toEqual(new MoneyAmount(Configuration::STARTKAPITAL_VALUE + $loanAmount));

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
        $takeoutLoanForm->totalRepayment = 12500;
        $takeoutLoanForm->repaymentPerKonjunkturphase = 625; // correct value
        $takeoutLoanForm->sumOfAllAssets = Configuration::STARTKAPITAL_VALUE;
        $takeoutLoanForm->zinssatz = 5;
        $takeoutLoanForm->loanId = LoanId::unique()->value;

        // player 0 takes out a loan
        $this->coreGameLogic->handle($this->gameId, TakeOutALoanForPlayer::create(
            $this->players[0],
            $takeoutLoanForm
        ));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var LoanWasTakenOutForPlayer $loanWasTakenOut */
        $loanWasTakenOut = $gameEvents->findLast(LoanWasTakenOutForPlayer::class);
        expect($loanWasTakenOut->getResourceChanges($this->players[0])->guthabenChange)->toEqual(new MoneyAmount(10000))
            ->and(PlayerState::getGuthabenForPlayer($gameEvents,
                $this->players[0]))->toEqual(new MoneyAmount(Configuration::STARTKAPITAL_VALUE + $loanAmount));
        $loanId = $loanWasTakenOut->loanId;

        // player 0 repays the loan
        $this->coreGameLogic->handle($this->gameId, RepayLoanForPlayer::create(
            $this->players[0],
            $loanId
        ));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        $expectedRepaymentCost = new MoneyAmount(-12625);

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
        $takeoutLoanForm->totalRepayment = 12500;
        $takeoutLoanForm->repaymentPerKonjunkturphase = 625; // correct value
        $takeoutLoanForm->sumOfAllAssets = Configuration::STARTKAPITAL_VALUE;
        $takeoutLoanForm->zinssatz = 5;
        $takeoutLoanForm->loanId = LoanId::unique()->value;

        // player 0 takes out a loan
        $this->coreGameLogic->handle($this->gameId, TakeOutALoanForPlayer::create(
            $this->players[0],
            $takeoutLoanForm
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
