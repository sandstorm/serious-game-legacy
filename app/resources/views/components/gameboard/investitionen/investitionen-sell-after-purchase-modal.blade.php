@extends ('components.modal.mandatory-modal', ['size' => "small"])

@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\InvestmentPriceState')

@props([
    'playerId' => null,
    'game-events' => null,
])

@section('title_mandatory')
    <span>
        Verkauf - {{ $this->sellInvestmentsForm->investmentId }} <i class="icon-aktien" aria-hidden="true"></i>
    </span>
@endsection

@section('icon_mandatory')
    <i class="icon-ereignis" aria-hidden="true"></i>
@endsection

@section('content_mandatory')
    <h4>{{ $this->sellInvestmentsForm->playerName }} hat in {{ $this->sellInvestmentsForm->investmentId }} investiert!</h4>
    @if ($this->sellInvestmentsForm->amountOwned > 0)
        <p>
            Du kannst jetzt deine Anteile verkaufen.
        </p>
    @endif

    <x-gameboard.investitionen.investitionen-sell-form
        :game-events="$gameEvents"
        unit="Anteil"
        sell-button-label="Anteile verkaufen"
        action="sellInvestmentsAfterPurchase('{{ $this->sellInvestmentsForm->investmentId }}')"
        :does-cost-zeitstein="false"
    />
@endsection

@section('footer_mandatory')
    <button type="button"
            @class([
                "button",
                "button--type-outline-primary",
                $this->getPlayerColorClass()
            ])
            wire:click="closeSellInvestmentsModal()"
    >
        Ich möchte nichts verkaufen
    </button>
@endsection
