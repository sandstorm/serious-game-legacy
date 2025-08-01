@extends ('components.modal.modal', ['closeModal' => "toggleStocksModal()", 'size' => 'medium'])

@use('Domain\CoreGameLogic\Feature\Spielzug\ValueObject\StockType')
@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\StockPriceState')
@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState')

@props([
    'gameEvents' => null,
])

@section('icon')
    <x-gameboard.phase-icon />
@endsection

@section('title')
    @if ($this->buyStocksOfType)
        <span>
            Kauf - {{ $this->buyStocksOfType->toPrettyString() }} Aktie <i class="icon-aktien" aria-hidden="true"></i>
        </span>
    @else
        <span>
            Aktien <i class="icon-aktien" aria-hidden="true"></i>
        </span>
        <span class="font-size--base">
            Investitionen
        </span>
    @endif
@endsection

@section('content')
    @if ($this->buyStocksOfType)
        <div class="buy-stocks">
            <form class="buy-stocks__form" wire:submit="buyStocks('{{ $this->buyStocksOfType }}')">
                <div class="buy-stocks__form-price">
                    {!! StockPriceState::getCurrentStockPrice($gameEvents, $this->buyStocksOfType)->format() !!} / Aktie
                </div>
                <div class="buy-stocks__form-amount">
                    <label for="buystocks.amount">Stückzahl Aktien</label>
                    <x-form.textfield wire:model="buyStocksForm.amount" id="buystocks.amount" name="buystocks.amount" type="number" step="1" />
                </div>
                <div class="buy-stocks__form-sum">
                    <strong>Summe Kauf</strong>
                    <span x-text="new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format($wire.buyStocksForm.amount * $wire.buyStocksForm.sharePrice)"></span>
                </div>
                <x-form.submit disabled wire:dirty.remove.attr="disabled">Aktien kaufen</x-form.submit>
            </form>
            @error('buyStocksForm.amount') <span class="form__error">{{ $message }}</span> @enderror

            <div class="buy-stocks__hints">
                @if ($this->buyStocksOfType === StockType::LOW_RISK)
                    <ul>
                        <li>Langfristige Tendenz: <strong>7%</strong></li>
                        <li>Kursschwankungen: <strong>15%</strong></li>
                        <li>Erwartungswert: <strong>-8% bis 22%</strong></li>
                        <li>Dividende pro Aktie: <strong>{!! KonjunkturphaseState::getCurrentKonjunkturphase($gameEvents)->getDividend()->format() !!}</strong></li>
                    </ul>
                @else
                    <ul>
                        <li>Langfristige Tendenz: <strong>9%</strong></li>
                        <li>Kursschwankungen: <strong>40%</strong> </li>
                        <li>Erwartungswert: <strong>-31% bis 49%</strong></li>
                        <li>Dividende pro Aktie: <strong>keine</strong></li>
                    </ul>
                @endif
            </div>
        </div>
    @else
        <p>
            Hier kannst du Aktien kaufen. Es gibt zwei Arten von Aktien: Low Risk und High Risk.
        </p>
        <div class="stock-types">
            <x-gameboard.investitionen.stock-type
                :stock-type="StockType::LOW_RISK"
                :game-Events="$gameEvents" />

            <x-gameboard.investitionen.stock-type
                :stock-type="StockType::HIGH_RISK"
                :game-Events="$gameEvents" />
        </div>
    @endif
@endsection
