<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\Dto\MoneySheet as MoneySheetDto;
use App\Livewire\Forms\MoneySheetInsurancesForm;
use App\Livewire\Forms\MoneySheetLebenshaltungskostenForm;
use App\Livewire\Forms\MoneySheetSteuernUndAbgabenForm;
use App\Livewire\Forms\TakeOutALoanForm;
use App\Livewire\ValueObject\ExpensesTabEnum;
use App\Livewire\ValueObject\IncomeTabEnum;
use App\Livewire\ValueObject\NotificationTypeEnum;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Moneysheet\State\LoanCalculator;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Moneysheet\ValueObject\LoanId;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\RepayLoanForPlayerAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\TakeOutALoanForPlayerAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\IsPlayerAllowedToTakeOutALoanValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\CancelInsuranceForPlayerAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\ConcludeInsuranceForPlayerAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Command\CancelInsuranceForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ConcludeInsuranceForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EnterLebenshaltungskostenForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\RepayLoanForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\TakeOutALoanForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EnterSteuernUndAbgabenForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LoanWasRepaidForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Insurance\InsuranceFinder;
use Domain\Definitions\Insurance\ValueObject\InsuranceId;
use Domain\Definitions\Konjunkturphase\ValueObject\AuswirkungScopeEnum;

trait HasMoneySheet
{
    // forms
    public MoneySheetLebenshaltungskostenForm $moneySheetLebenshaltungskostenForm;
    public MoneySheetSteuernUndAbgabenForm $moneySheetSteuernUndAbgabenForm;
    public MoneySheetInsurancesForm $moneySheetInsurancesForm;
    public TakeOutALoanForm $takeOutALoanForm;

    public bool $moneySheetIsVisible = false;
    public bool $editIncomeIsVisible = false;
    public bool $editExpensesIsVisible = false;
    public bool $takeOutALoanIsVisible = false;
    public ?string $repaymentFormForLoanId = null;

    // set in the view money-sheet-income.blade.php
    public IncomeTabEnum $activeTabForIncome = IncomeTabEnum::INVESTMENTS;
    // set in the view money-sheet-expenses.blade.php
    public ExpensesTabEnum $activeTabForExpenses = ExpensesTabEnum::LOANS;

    public function showMoneySheet(): void
    {
        $this->moneySheetIsVisible = true;
    }

    public function closeMoneySheet(): void
    {
        $this->moneySheetIsVisible = false;
        $this->editIncomeIsVisible = false;
        $this->editExpensesIsVisible = false;
        $this->takeOutALoanIsVisible = false;
        $this->repaymentFormForLoanId = null;
    }

    public function toggleEditIncome(): void
    {
        $this->editIncomeIsVisible = !$this->editIncomeIsVisible;
        if ($this->editIncomeIsVisible) {
            $this->showIncomeTab(IncomeTabEnum::INVESTMENTS->value);
        }
    }

    public function toggleEditExpenses(): void
    {
        $this->editExpensesIsVisible = !$this->editExpensesIsVisible;
        if ($this->editExpensesIsVisible) {
            $this->showExpensesTab(ExpensesTabEnum::LOANS->value);
        }
    }

    public function showIncomeTab(string $tab): void
    {
        $this->editExpensesIsVisible = false;
        $this->takeOutALoanIsVisible = false;
        $this->repaymentFormForLoanId = null;

        $this->moneySheetIsVisible = true;
        $this->editIncomeIsVisible = true;
        $this->activeTabForIncome = IncomeTabEnum::from($tab);
    }

    public function showExpensesTab(string $tab): void
    {
        $tab = ExpensesTabEnum::from($tab);
        $this->activeTabForExpenses = $tab;
        $this->moneySheetIsVisible = true;
        $this->editIncomeIsVisible = false;
        $this->editExpensesIsVisible = true;
        $this->takeOutALoanIsVisible = false;
        $this->repaymentFormForLoanId = null;

        match($tab) {
            ExpensesTabEnum::LIVING_COSTS => $this->initializeLivingCostsForm(),
            ExpensesTabEnum::TAXES => $this->initializeTaxesForm(),
            ExpensesTabEnum::INSURANCES => $this->initializeInsurancesForm(),
            default => null
        };
    }

    /**
     * Returns true, if the player can take out a loan. Use this to disable buttons that open
     * the loan form modal.
     * @return bool
     */
    public function isPlayerAllowedToTakeOutALoan(): bool
    {
        $validator = new IsPlayerAllowedToTakeOutALoanValidator();
        return $validator->validate($this->gameEvents, $this->myself)->canExecute;
    }

    /**
     * Opens the TakeOutALoan Form, if the player is allowed to take out a loan.
     * Displays an error message otherwise.
     * @return void
     */
    public function showTakeOutALoan(): void
    {
        $takeOutALoanAktion = new TakeOutALoanForPlayerAktion();
        $validationResult = $takeOutALoanAktion->validate($this->myself, $this->getGameEvents());
        if (!$validationResult->canExecute) {
            $this->showNotification(
                $validationResult->reason,
                NotificationTypeEnum::ERROR
            );
            // Don't do anything else
            return;
        }

        $this->moneySheetIsVisible = false;
        $this->editIncomeIsVisible = false;
        $this->editExpensesIsVisible = false;
        $this->repaymentFormForLoanId = null;
        $this->takeOutALoanIsVisible = true;
        $this->initTakeOutALoanForm();
    }

    public function closeTakeOutALoan(): void
    {
        $this->showExpensesTab(ExpensesTabEnum::LOANS->value);
    }

    public function showRepayLoan(string $loanId): void
    {
        $this->moneySheetIsVisible = false;
        $this->editIncomeIsVisible = false;
        $this->editExpensesIsVisible = false;
        $this->takeOutALoanIsVisible = false;
        $this->repaymentFormForLoanId = $loanId;
    }

    public function closeRepayLoan(): void
    {
        $this->showExpensesTab(ExpensesTabEnum::LOANS->value);
    }

    public function getMoneysheetForPlayerId(PlayerId $playerId): MoneySheetDto
    {
        $totalFromPlayerInput = MoneySheetState::calculateTotalFromPlayerInput($this->getGameEvents(), $playerId);
        $guthabenAfterKonjunkturphaseChange = PlayerState::getGuthabenForPlayer($this->getGameEvents(), $playerId);
        $guthabenBeforeKonjunkturphaseChange = $guthabenAfterKonjunkturphaseChange->subtract($totalFromPlayerInput);
        return new MoneySheetDto(
            lebenshaltungskosten: new MoneyAmount(-1 * MoneySheetState::getLastInputForLebenshaltungskosten($this->getGameEvents(), $playerId)->value),
            doesLebenshaltungskostenRequirePlayerAction: MoneySheetState::doesLebenshaltungskostenRequirePlayerAction($this->getGameEvents(), $playerId),
            steuernUndAbgaben: new MoneyAmount(-1 * MoneySheetState::getLastInputForSteuernUndAbgaben($this->getGameEvents(), $playerId)->value),
            doesSteuernUndAbgabenRequirePlayerAction: MoneySheetState::doesSteuernUndAbgabenRequirePlayerAction($this->getGameEvents(), $playerId),
            gehalt: PlayerState::getCurrentGehaltForPlayer($this->getGameEvents(), $playerId),
            totalFromPlayerInput: $totalFromPlayerInput,
            totalInsuranceCost: new MoneyAmount(-1 * MoneySheetState::getCostOfAllInsurances($this->getGameEvents(), $playerId)->value),
            annualExpensesForAllLoans: new MoneyAmount(-1 * MoneySheetState::getAnnualExpensesForAllLoans($this->getGameEvents(), $playerId)->value),
            annualIncomeForAllAssets: MoneySheetState::getAnnualIncomeForAllInvestments($this->getGameEvents(), $playerId),
            annualIncome: MoneySheetState::getAnnualIncomeForPlayer($this->getGameEvents(), $playerId),
            annualExpenses: new MoneyAmount(-1 * MoneySheetState::getAnnualExpensesForPlayer($this->getGameEvents(), $playerId)->value),
            annualExpensesFromPlayerInput: new MoneyAmount (-1 * MoneySheetState::calculateAnnualExpensesFromPlayerInput($this->getGameEvents(), $playerId)->value),
            guthabenBeforeKonjunkturphaseChange: $guthabenBeforeKonjunkturphaseChange,
            guthabenAfterKonjunkturphaseChange: $guthabenAfterKonjunkturphaseChange,
        );
    }

    public function setLebenshaltungskosten(): void
    {
        $this->moneySheetLebenshaltungskostenForm->validate();
        $this->handleCommand(EnterLebenshaltungskostenForPlayer::create(
            $this->myself,
            new MoneyAmount($this->moneySheetLebenshaltungskostenForm->lebenshaltungskosten)
        ));

        $updatedEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $resultOfLastInput = MoneySheetState::getResultOfLastLebenshaltungskostenInput($updatedEvents, $this->myself);

        if (!$resultOfLastInput->wasSuccessful && $resultOfLastInput->fine->value > 0) {
            $this->moneySheetLebenshaltungskostenForm->addError('lebenshaltungskosten',
                "Du hast einen falschen Wert für die Lebenshaltungskosten eingegeben. Dir wurden {$resultOfLastInput->fine->value} € abgezogen. Wir haben den Wert für dich korrigiert.");
        } elseif (!$resultOfLastInput->wasSuccessful) {
            $this->moneySheetLebenshaltungskostenForm->addError('lebenshaltungskosten',
                "Du hast einen falschen Wert für die Lebenshaltungskosten eingegeben.");
        }

        $this->initializeLivingCostsForm();
        $this->broadcastNotify();
    }

    public function setSteuernUndAbgaben(): void
    {
        $this->moneySheetSteuernUndAbgabenForm->validate();
        $this->handleCommand(EnterSteuernUndAbgabenForPlayer::create(
            $this->myself,
            new MoneyAmount($this->moneySheetSteuernUndAbgabenForm->steuernUndAbgaben)
        ));

        $updatedEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $resultOfLastInput = MoneySheetState::getResultOfLastSteuernUndAbgabenInput($updatedEvents, $this->myself);

        if (!$resultOfLastInput->wasSuccessful && $resultOfLastInput->fine->value > 0) {
            $this->moneySheetSteuernUndAbgabenForm->addError('steuernUndAbgaben',
                "Du hast einen falschen Wert für die Steuern und Abgaben eingegeben. Dir wurden {$resultOfLastInput->fine->value} € abgezogen. Wir haben den Wert für dich korrigiert.");
        } elseif (!$resultOfLastInput->wasSuccessful) {
            $this->moneySheetSteuernUndAbgabenForm->addError('steuernUndAbgaben',
                "Du hast einen falschen Wert für die Steuern und Abgaben eingegeben.");
        }

        $this->initializeTaxesForm();
        $this->broadcastNotify();
    }

    public function setInsurances(): void
    {
        foreach($this->moneySheetInsurancesForm->insurances as $insuranceFromForm) {
            $insuranceId = InsuranceId::create($insuranceFromForm['id']);
            $shouldBeConcluded = $insuranceFromForm['value'] === true;
            $currentlyConcluded = MoneySheetState::doesPlayerHaveThisInsurance($this->getGameEvents(), $this->myself, $insuranceId);
            if ($currentlyConcluded === $shouldBeConcluded) {
                // nothing to do, insurance is already in the desired state
                continue;
            }
            // conclude or cancel insurance
            if ($shouldBeConcluded) {
                $concludeInsuranceValidationResult = new ConcludeInsuranceForPlayerAktion($insuranceId)->validate($this->myself, $this->getGameEvents());
                if ($concludeInsuranceValidationResult->canExecute) {
                    $this->handleCommand(ConcludeInsuranceForPlayer::create($this->myself, $insuranceId));
                } else {
                    $insuranceName = InsuranceFinder::getInstance()->findInsuranceById($insuranceId)->description;
                    $this->showBanner("Du kannst die " . $insuranceName . " nicht abschließen: " . $concludeInsuranceValidationResult->reason);
                }
            } else {
                $cancelInsuranceValidationResult = new CancelInsuranceForPlayerAktion($insuranceId)->validate($this->myself, $this->getGameEvents());
                if ($cancelInsuranceValidationResult->canExecute) {
                    $this->handleCommand(CancelInsuranceForPlayer::create($this->myself, $insuranceId));
                }else {
                    $insuranceName = InsuranceFinder::getInstance()->findInsuranceById($insuranceId)->description;
                    $this->showBanner('Du kannst die ' . $insuranceName . ' nicht kündigen: ' . $cancelInsuranceValidationResult->reason);
                }
            }
        }
        $this->broadcastNotify();
    }

    public function takeOutALoan(): void
    {
        $loanId = new LoanId($this->takeOutALoanForm->loanId);
        $this->handleCommand(TakeOutALoanForPlayer::create(
            $this->myself,
            $this->takeOutALoanForm
        ));

        $updatedEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $resultOfLastInput = MoneySheetState::getResultOfLastLoanInput($updatedEvents, $this->myself, $loanId);

        if (!$resultOfLastInput->wasSuccessful && $resultOfLastInput->fine->value > 0) {
            $this->takeOutALoanForm->loanAmount = intval(min(
                $this->takeOutALoanForm->loanAmount,
                LoanCalculator::getMaxLoanAmount($this->takeOutALoanForm->sumOfAllAssets, $this->takeOutALoanForm->salary, $this->takeOutALoanForm->obligations, $this->takeOutALoanForm->wasPlayerInsolventInThePast)->value
            ));
            $this->takeOutALoanForm->totalRepayment = LoanCalculator::getCalculatedTotalRepayment($this->takeOutALoanForm->loanAmount, $this->takeOutALoanForm->zinssatz);
            $this->takeOutALoanForm->repaymentPerKonjunkturphase = $this->takeOutALoanForm->getCalculatedRepaymentPerKonjunkturphase();

            // reset old validation errors when correcting the input
            $this->takeOutALoanForm->resetValidation();
            $this->takeOutALoanForm->generalError = "Du hast falsche Werte für den Kredit eingegeben. Dir wurden {$resultOfLastInput->fine->value} € abgezogen. Wir haben die Werte für dich korrigiert.";
        } elseif (!$resultOfLastInput->wasSuccessful) {
            $this->takeOutALoanForm->generalError = "Du hast falsche Werte für den Kredit eingegeben.";
        } else {
            $this->takeOutALoanForm->resetValidation();
            $loanAmount = new MoneyAmount($this->takeOutALoanForm->loanAmount);
            $this->showBanner("Du hast einen Kredit über {$loanAmount->formatWithoutHtml()} aufgenommen.");
            $this->closeTakeOutALoan();
        }

        $this->broadcastNotify();
    }

    public function repayLoan(string $loanId): void
    {
        $repayAktion = new RepayLoanForPlayerAktion(new LoanId($loanId));
        $validationResult = $repayAktion->validate($this->myself, $this->getGameEvents());
        if (!$validationResult->canExecute) {
            $this->showNotification(
                $validationResult->reason,
                NotificationTypeEnum::ERROR
            );
            return;
        }

        $this->handleCommand(RepayLoanForPlayer::create(
            $this->myself,
            new LoanId($loanId)
        ));

        $repaymentEvent = $this->getGameEvents()->findLast(LoanWasRepaidForPlayer::class);
        $this->showBanner("Du hast deinen Kredit komplett zurückgezahlt.", $repaymentEvent->getResourceChanges($this->myself));
        $this->closeRepayLoan();
        $this->broadcastNotify();
    }

    private function initTakeOutALoanForm(): void
    {
        $this->takeOutALoanForm->reset();
        $this->takeOutALoanForm->resetValidation();
        $this->takeOutALoanForm->loanId = LoanId::unique()->value;
        $this->takeOutALoanForm->sumOfAllAssets = PlayerState::getTotalValueOfAllAssetsForPlayer($this->getGameEvents(), $this->myself)->value + PlayerState::getGuthabenForPlayer($this->getGameEvents(), $this->myself)->value;
        $this->takeOutALoanForm->salary = PlayerState::getCurrentGehaltForPlayer($this->getGameEvents(), $this->myself)->value;
        $this->takeOutALoanForm->zinssatz = KonjunkturphaseState::getCurrentKonjunkturphase($this->getGameEvents())->getAuswirkungByScope(AuswirkungScopeEnum::LOANS_INTEREST_RATE)->value;
        $this->takeOutALoanForm->obligations = MoneySheetState::getTotalOpenRepaymentValueForAllLoans($this->getGameEvents(), $this->myself)->value;
        $this->takeOutALoanForm->wasPlayerInsolventInThePast = PlayerState::wasPlayerInsolventInThePast($this->getGameEvents(), $this->myself);
    }

    private function initializeTaxesForm(): void
    {
        $latestInputForSteuernUndAbgaben = MoneySheetState::getLastInputForSteuernUndAbgaben($this->getGameEvents(), $this->myself);
        $calculatedSteuernUndAbgaben = MoneySheetState::calculateSteuernUndAbgabenForPlayer($this->getGameEvents(), $this->myself);
        $this->moneySheetSteuernUndAbgabenForm->steuernUndAbgaben = $latestInputForSteuernUndAbgaben->value;
        $this->moneySheetSteuernUndAbgabenForm->isSteuernUndAbgabenInputDisabled = $latestInputForSteuernUndAbgaben->equals($calculatedSteuernUndAbgaben);
    }

    private function initializeLivingCostsForm(): void
    {
        $latestInputForLebenshaltungskosten = MoneySheetState::getLastInputForLebenshaltungskosten($this->getGameEvents(), $this->myself);
        $calculatedLebenshaltungskosten = MoneySheetState::calculateLebenshaltungskostenForPlayer($this->getGameEvents(), $this->myself);
        $this->moneySheetLebenshaltungskostenForm->lebenshaltungskosten = $latestInputForLebenshaltungskosten->value;
        $this->moneySheetLebenshaltungskostenForm->isLebenshaltungskostenInputDisabled = $latestInputForLebenshaltungskosten->equals($calculatedLebenshaltungskosten);
    }

    private function initializeInsurancesForm(): void
    {
        $insurances = InsuranceFinder::getInstance()->getAllInsurances();
        $currentPlayerPhase = PlayerState::getCurrentLebenszielphaseIdForPlayer($this->getGameEvents(), $this->myself)->value;
        foreach ($insurances as $insurance) {
            $isActive = MoneySheetState::doesPlayerHaveThisInsurance($this->getGameEvents(), $this->myself, $insurance->id);
            $this->moneySheetInsurancesForm->addInsurance(
                $currentPlayerPhase,
                $insurance,
                $isActive
            );
        }
    }
}
