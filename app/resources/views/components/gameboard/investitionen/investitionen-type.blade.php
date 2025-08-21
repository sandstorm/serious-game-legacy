@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\InvestmentPriceState')
@use('Domain\Definitions\Investments\ValueObject\InvestmentId')

@props([
    'investmentType' => null,
    'gameEvents' => null,
    'unit' => 'Aktie',
])

<div class="investitionen-type">
    <div>
        <h4>{{ $investmentType }}</h4>
        <div class="resource-change">
            {!! InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, $investmentType)->format() !!} / {{ $unit }}
        </div>
    </div>
    <div class="investitionen-type__actions">
        <button
            type="button"
            @class([
                "button",
                "button--type-primary",
                "button--disabled" => !$this->canBuyInvestments($investmentType)->canExecute,
                $this->getPlayerColorClass(),
            ])
            wire:click="showBuyInvestmentOfType('{{ $investmentType }}')"
        >
            kaufen
        </button>
        <button
            type="button"
            @class([
                "button",
                "button--type-outline-primary",
                "button--disabled" => !$this->canSellInvestments($investmentType)->canExecute,
                $this->getPlayerColorClass(),
            ])
            wire:click="showSellInvestmentOfType('{{ $investmentType }}')"
        >
            verkaufen
        </button>
    </div>

</div>
