@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState')
@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\InvestmentPriceState')
@use('Domain\Definitions\Investments\ValueObject\InvestmentId')
@use('Domain\Definitions\Configuration\Configuration')

@props([
    'gameEvents' => null,
    'investment' => null,
    'unit' => 'Aktie',
    'buyButtonLabel' => 'Aktien kaufen',
])

<p>
    {{ $investment->description }}
</p>

<form class="investitionen-form" wire:submit="buyInvestments('{{ $this->buyInvestmentOfType }}')">
    <div class="investitionen-form__price">
        {!! InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, $this->buyInvestmentOfType)->format() !!} /
        {{ $unit }}
    </div>
    <div class="investitionen-form__amount">
        <label for="buyInvestments.amount">Stückzahl</label>
        <x-form.textfield wire:model="buyInvestmentsForm.amount" id="buyInvestments.amount" name="buyInvestments.amount"
                          type="number" step="1" min="1" max="{{ Configuration::MAX_INPUT_VALUE }}"/>
    </div>
    <div class="investitionen-form__sum">
        <strong>Summe Kauf</strong>
        <span
            x-text="new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format($wire.buyInvestmentsForm.amount * $wire.buyInvestmentsForm.sharePrice)"></span>
    </div>
    <x-form.submit disabled wire:dirty.remove.attr="disabled">
        {{ $buyButtonLabel }}
        <div class="button__suffix">
            <div>
                <i class="icon-minus text--danger" aria-hidden="true"></i><i class="icon-zeitstein" aria-hidden="true"></i>
                <span class="sr-only">, kostet einen Zeitstein</span>
            </div>
        </div>
    </x-form.submit>
</form>
@error('buyInvestmentsForm.amount') <span class="form__error">{{ $message }}</span> @enderror

<div class="investitionen-form__hints">
    <ul>
        <li>Langfristige Tendenz: <strong>{{ $investment->longTermTrend }}%</strong></li>
        <li>Kursschwankungen: <strong>{{ $investment->fluctuations }}%</strong></li>
        <li>Dividende pro {{ $unit }}:
            @if ($this->buyInvestmentOfType === InvestmentId::MERFEDES_PENZ)
                <strong>{!! KonjunkturphaseState::getCurrentKonjunkturphase($gameEvents)->getDividend()->format() !!}</strong>
            @else
                <strong>/</strong>
            @endif
        </li>
    </ul>
</div>
