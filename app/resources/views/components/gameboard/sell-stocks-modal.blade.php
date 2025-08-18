@extends ('components.modal.mandatory-modal')

@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\StockPriceState')

@props([
    'playerId' => null,
    'game-events' => null,
])

@section('title_mandatory')
    <span>
        Verkauf - {{ $this->sellStocksForm->stockType->toPrettyString() }} Aktie <i class="icon-aktien" aria-hidden="true"></i>
    </span>
@endsection

@section('icon_mandatory')
    <i class="icon-ereignis" aria-hidden="true"></i>
@endsection

@section('content_mandatory')
    <h4>Ein anderer Spieler hat Aktien vom Typ {{ $this->sellStocksForm->stockType->toPrettyString() }} gekauft!</h4>
    @if ($this->sellStocksForm->amountOwned > 0)
        <p>
            Du kannst jetzt deine Aktien verkaufen.  <br />
            Du hast aktuell <strong>{{ $this->sellStocksForm->amountOwned }}</strong> Aktien vom Typ <strong>{{ $this->sellStocksForm->stockType->toPrettyString() }}</strong> in deinem Besitz.
        </p>
        <hr />
        <form class="stocks__form" wire:submit="sellStocks('{{ $this->sellStocksForm->stockType }}')">
            <div class="stocks__form-price">
                {!! StockPriceState::getCurrentStockPrice($gameEvents, $this->sellStocksForm->stockType)->format() !!} / Aktie
            </div>
            <div class="stocks__form-amount">
                <label for="sellStocks.amount">Stückzahl Aktien</label>
                <x-form.textfield wire:model="sellStocksForm.amount" id="sellStocks.amount" name="sellStocks.amount" type="number" step="1" />
            </div>
            <div class="stocks__form-sum">
                <strong>Summe Verkauf</strong>
                <span x-text="new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format($wire.sellStocksForm.amount * $wire.sellStocksForm.sharePrice)"></span>
            </div>
            <x-form.submit disabled wire:dirty.remove.attr="disabled">Aktien verkaufen</x-form.submit>
        </form>
        @error('sellStocksForm.amount') <span class="form__error">{{ $message }}</span> @enderror
    @else
        <p>
            Du hast keine Aktien vom Typ {{ $this->sellStocksForm->stockType->toPrettyString() }}.
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
            wire:click="closeSellStocksModal()"
    >
        Ich möchte keine Aktien verkaufen
    </button>
@endsection
