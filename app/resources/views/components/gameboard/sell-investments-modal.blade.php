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
            Du kannst jetzt deine Anteile verkaufen. <br/>
            Du hast aktuell <strong>{{ $this->sellInvestmentsForm->amountOwned }}</strong> Anteile vom Typ
            <strong>{{ $this->sellInvestmentsForm->investmentId }}</strong> in deinem Besitz.
        </p>
        <hr/>
        <form class="investments__form" wire:submit="sellInvestments('{{ $this->sellInvestmentsForm->investmentId }}')">
            <div class="investments__form-price">
                {!! InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, $this->sellInvestmentsForm->investmentId)->format() !!}
                / Anteil
            </div>
            <div class="investments__form-amount">
                <label for="sellInvestments.amount">Stückzahl</label>
                <x-form.textfield wire:model="sellInvestmentsForm.amount" id="sellInvestments.amount" name="sellInvestments.amount"
                                  type="number" step="1" min="1" max="2147483647"/>
            </div>
            <div class="investments__form-sum">
                <strong>Summe Verkauf</strong>
                <span
                    x-text="new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format($wire.sellInvestmentsForm.amount * $wire.sellInvestmentsForm.sharePrice)"></span>
            </div>
            <x-form.submit disabled wire:dirty.remove.attr="disabled">Anteile verkaufen</x-form.submit>
        </form>
        @error('sellInvestmentsForm.amount') <span class="form__error">{{ $message }}</span> @enderror
    @else
        <p>
            Du hast keine Anteile vom Typ {{ $this->sellInvestmentsForm->investmentId }}.
        </p>
    @endif
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
