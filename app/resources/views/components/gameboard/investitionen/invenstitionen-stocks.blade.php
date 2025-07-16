@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')
@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\StockPriceState')
@use('Domain\CoreGameLogic\Feature\Spielzug\ValueObject\StockType')

@props([
    'gameEvents' => null,
    'playerId' => null,
])

<h3>Aktien kaufen</h3>
<p>
    Dein Guthaben: {!! PlayerState::getGuthabenForPlayer($gameEvents, $playerId)->format() !!}
</p>
<div @style(['display:flex', 'flex-wrap:no-wrap', 'gap:1rem'])>
    <div>
        <h4>Low Risk</h4>

        @if (!$this->canBuyStocks(StockType::LOW_RISK)->canExecute)
            <div class="form__error">
                Du kannst akuell keine Low Risk Aktien kaufen: {{ $this->canBuyStocks(StockType::LOW_RISK)->reason }}
            </div>
        @endif

        <ul>
            <li>Handelsspanne: 10-50 €</li>
            <li>Erwartete Jahresrendite: 7% - 9%</li>
            <li>Jahresvolatilität: 15% </li>
            <li>Erwartungswert: -8% bis 22%</li>
        </ul>
        <p>
            Aktueller Preis: {!! StockPriceState::getCurrentStockPrice($gameEvents, StockType::LOW_RISK)->format() !!}
        </p>

        <form wire:submit="buyLowRiskStocks">
            <div class="form__group">
                <label for="amount">Anzahl</label>
                <x-form.textfield wire:model="buyLowRiskStocksForm.amount" id="amount" name="amount" type="number" step="1" :disabled="!$this->canBuyStocks(StockType::LOW_RISK)->canExecute" />
                @error('buyLowRiskStocksForm.amount') <span class="form__error">{{ $message }}</span> @enderror
            </div>
            <x-form.submit :disabled="!$this->canBuyStocks(StockType::LOW_RISK)->canExecute">Low Risk Aktien kaufen</x-form.submit>
        </form>
    </div>
    <div>
        <h4>High Risk</h4>

        @if (!$this->canBuyStocks(StockType::HIGH_RISK)->canExecute)
            <div class="form__error">
                Du kannst akuell keine High Risk Aktien kaufen: {{ $this->canBuyStocks(StockType::HIGH_RISK)->reason }}
            </div>
        @endif


        <ul>
            <li>Handelsspanne: 10-50 € </li>
            <li>Erwartete Jahresrendite: keine </li>
            <li>Jahresvolatilität: 40% </li>
            <li>Erwartungswert: -31% bis 49%</li>
        </ul>
        <p>
            Aktueller Preis: {!! StockPriceState::getCurrentStockPrice($gameEvents, StockType::HIGH_RISK)->format() !!}
        </p>

        <form wire:submit="buyHighRiskStocks">
            <div class="form__group">
                <label for="amount">Anzahl</label>
                <x-form.textfield wire:model="buyHighRiskStocksForm.amount" id="amount" name="amount" type="number" step="1" :disabled="!$this->canBuyStocks(StockType::HIGH_RISK)->canExecute" />
                @error('buyHighRiskStocksForm.amount') <span class="form__error">{{ $message }}</span> @enderror
            </div>
            <x-form.submit :disabled="!$this->canBuyStocks(StockType::HIGH_RISK)->canExecute">High Risk Aktien kaufen</x-form.submit>
        </form>
    </div>
</div>
