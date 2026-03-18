@extends ('components.modal.modal', ["closeModal" => "closeRepayLoan()", "type" => "borderless"])

@use('Domain\CoreGameLogic\Feature\Moneysheet\State\LoanCalculator')
@use('Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState')
@use('Domain\CoreGameLogic\Feature\Moneysheet\ValueObject\LoanId')
@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')

@props([
    'gameEvents' => null,
    'playerId' => null,
])

@section('icon')
    Kredit zurückzahlen
@endsection

@section('content')
    <div class="repay-loan">
        <div class="take-out-loan__info-box">
            <i class="icon-info-2" aria-hidden="true"></i>
            <div class="take-out-loan__info-section">
                <small>Dein Guthaben</small>
                <span class="badge-with-background">
                    <x-money-amount :value="PlayerState::getGuthabenForPlayer($this->getGameEvents(), $this->myself)" />
                </span>
            </div>
        </div>

        <div class="repay-loan__amount">
            <strong>Rückzahlungssumme</strong>
            <span class="badge-with-background">
                <x-money-amount :value="LoanCalculator::getCostsForLoanRepayment(
                    MoneySheetState::getOpenRepaymentValueForLoan($gameEvents, $playerId, new LoanId($this->repaymentFormForLoanId))->value,
                )" />
            </span>
            <p>
                Rückzahlungssumme = <br />
                <strong>Restbetrag + 1 % * Restbetrag</strong>
            </p>
        </div>
    </div>
    <div class="repay-loan__actions">
        <button @class([
            "button",
            "button--type-outline-primary",
            $this->getPlayerColorClass(),
        ]) wire:click="closeRepayLoan()">
            Abbrechen
        </button>
        <button @class([
            "button",
            "button--type-primary",
            $this->getPlayerColorClass(),
        ]) wire:click="repayLoan('{{ $this->repaymentFormForLoanId }}')">
            Kredit jetzt zurückzahlen
        </button>
    </div>
@endsection
