@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState')
@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\InvestmentPriceState')
@use('Domain\Definitions\Investments\ValueObject\InvestmentId')

@props([
    'gameEvents' => null,
    'investment' => null,
    'unit' => 'Aktie',
    'buyButtonLabel' => 'Aktien kaufen',
])

<p>
    {{ $investment->description }}
</p>

<form class="investments__form" wire:submit="buyInvestments('{{ $this->buyInvestmentOfType }}')">
    <div class="investments__form-price">
        {!! InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, $this->buyInvestmentOfType)->format() !!} /
        {{ $unit }}
    </div>
    <div class="investments__form-amount">
        <label for="buyInvestments.amount">Stückzahl</label>
        <x-form.textfield wire:model="buyInvestmentsForm.amount" id="buyInvestments.amount" name="buyInvestments.amount"
                          type="number" step="1" min="1" max="2147483647"/>
    </div>
    <div class="investments__form-sum">
        <strong>Summe Kauf</strong>
        <span
            x-text="new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format($wire.buyInvestmentsForm.amount * $wire.buyInvestmentsForm.sharePrice)"></span>
    </div>
    <x-form.submit disabled wire:dirty.remove.attr="disabled">{{ $buyButtonLabel }}</x-form.submit>
</form>
@error('buyInvestmentsForm.amount') <span class="form__error">{{ $message }}</span> @enderror

<div class="buy-investments__hints">
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
