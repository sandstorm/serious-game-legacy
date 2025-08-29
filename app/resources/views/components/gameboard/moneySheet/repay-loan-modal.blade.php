@extends ('components.modal.modal', ["closeModal" => "closeRepayLoan()", "type" => "borderless"])

@use('Domain\CoreGameLogic\Feature\Moneysheet\State\LoanCalculator')
@use('Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState')
@use('Domain\CoreGameLogic\Feature\Moneysheet\ValueObject\LoanId')

@props([
    'gameEvents' => null,
    'playerId' => null,
])

@section('icon')
    Kredit zurückzahlen
@endsection

@section('content')
    <h4>
        Diesen Kredit komplett zurück zahlen kostet dich:
    </h4>

    <span class="badge-with-background">
        {!! LoanCalculator::getCostsForLoanRepayment(
            MoneySheetState::getOpenRepaymentValueForLoan($gameEvents, $playerId, new LoanId($this->showRepaymentFormForLoan))->value,
        )->format() !!}
    </span>
    <button @class([
        "button",
        "button--type-primary",
        $this->getPlayerColorClass(),
    ]) wire:click="repayLoan('{{ $this->showRepaymentFormForLoan }}')">
        Kredit jetzt zurückzahlen
    </button>
@endsection
