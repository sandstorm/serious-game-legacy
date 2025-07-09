@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')


@props([
    'gameEvents' => null,
    'playerId' => null,
])

<h3>Aktien kaufen</h3>
<p>
    Dein Guthaben: {!! PlayerState::getGuthabenForPlayer($gameEvents, $playerId)->format() !!}
</p>
<div>
    <h4>Low Risk</h4>
    <p>
        Handelsspanne: 10-50 €
    </p>
    <p>
        Aktueller Preis: {!! $this->getLowRiskStocksPrice()->format() !!}
    </p>

    <form wire:submit="buyLowRiskStocks">
        <div class="form__group">
            <label for="amount">Anzahl</label>
            <x-form.textfield wire:model="buyLowRiskStocksForm.amount" id="amount" name="amount" type="number" step="1" />
            @error('buyLowRiskStocksForm.amount') <span class="form__error">{{ $message }}</span> @enderror
        </div>
        <button>
            <x-form.submit>Low Risk Aktien kaufen</x-form.submit>
        </button>
    </form>
</div>
<hr />
<div>
    <h4>High Risk</h4>
    <p>
        Handelsspanne: 10-50 €
    </p>
    <p>
        Aktueller Preis: {!! $this->getHighRiskStocksPrice()->format() !!}
    </p>

    <form wire:submit="buyHighRiskStocks">
        <div class="form__group">
            <label for="amount">Anzahl</label>
            <x-form.textfield wire:model="buyHighRiskStocksForm.amount" id="amount" name="amount" type="number" step="1" />
            @error('buyHighRiskStocksForm.amount') <span class="form__error">{{ $message }}</span> @enderror
        </div>
        <button>
            <x-form.submit>High Risk Aktien kaufen</x-form.submit>
        </button>
    </form>
</div>

