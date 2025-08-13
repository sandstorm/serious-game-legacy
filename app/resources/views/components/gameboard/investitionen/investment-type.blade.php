@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\InvestmentPriceState')
@use('Domain\Definitions\Investments\ValueObject\InvestmentId')

@props([
    'investmentType' => null,
    'gameEvents' => null,
    'unit' => 'Aktie',
])

<div class="investment-type">
    <div>
        <h4>{{ $investmentType }}</h4>
        <div class="resource-change">
            {!! InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, $investmentType)->format() !!} / {{ $unit }}
        </div>
    </div>
    <button
        type="button"
        @class([
            "button",
            "button--type-primary",
            "button--disabled" => !$this->canBuyInvestments($investmentType)->canExecute,
            $this->getPlayerColorClass(),
        ])
        wire:click="showbuyInvestmentOfType('{{ $investmentType }}')"
    >
        wählen
    </button>
</div>
