<?php
declare(strict_types=1);

use App\Livewire\Forms\TakeOutALoanForm;

use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
use Domain\CoreGameLogic\Feature\Moneysheet\ValueObject\LoanId;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\CompleteMoneysheetForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EnterLebenshaltungskostenForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\FileInsolvenzForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\TakeOutALoanForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\LoanData;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LoanForPlayerWasCorrected;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LoanForPlayerWasEntered;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
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
});
