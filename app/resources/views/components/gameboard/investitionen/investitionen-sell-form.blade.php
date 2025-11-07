@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState')
@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\InvestmentPriceState')
@use('Domain\Definitions\Investments\ValueObject\InvestmentId')
@use('Domain\Definitions\Configuration\Configuration')

@props([
    'gameEvents' => null,
    'unit' => 'Aktie',
    'sellButtonLabel' => 'Aktien verkaufen',
    'action' => null,
    'doesCostZeitstein' => true,
])

@if ($this->sellInvestmentsForm->amountOwned > 0)
    <p>
        Du hast aktuell <strong>{{ $this->sellInvestmentsForm->amountOwned }}</strong> Anteile vom Typ
        <strong>{{ $this->sellInvestmentsForm->investmentId }}</strong> in deinem Besitz.
    </p>
    <form class="investitionen-form" wire:submit={{$action}}>
        <div class="investitionen-form__price">
            {!! InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, $this->sellInvestmentsForm->investmentId)->format() !!} /
            {{ $unit }}
        </div>
        <div class="investitionen-form__amount">
            <label for="sellInvestments.amount">Stückzahl</label>
            <x-form.textfield wire:model="sellInvestmentsForm.amount" id="sellInvestments.amount" name="sellInvestments.amount"
                              type="number" step="1" min="1" max="{{ Configuration::MAX_INPUT_VALUE }}"/>
        </div>
        <div class="investitionen-form__sum">
            <strong>Summe Verkauf</strong>
            <span
                x-text="new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format($wire.sellInvestmentsForm.amount * $wire.sellInvestmentsForm.sharePrice)"></span>
        </div>
        <x-form.submit disabled wire:dirty.remove.attr="disabled">
            {{ $sellButtonLabel }}
            @if ($doesCostZeitstein)
                <div class="button__suffix">
                    <div>
                        <i class="icon-minus text--danger" aria-hidden="true"></i><i class="icon-zeitstein" aria-hidden="true"></i>
                        <span class="sr-only">, kostet einen Zeitstein</span>
                    </div>
                </div>
            @endif
        </x-form.submit>
    </form>
    @error('sellInvestmentsForm.amount') <span class="form-error">{{ $message }}</span> @enderror
@else
    <p>
        Du hast keine Anteile vom Typ {{ $this->sellInvestmentsForm->investmentId }}.
    </p>
@endif
