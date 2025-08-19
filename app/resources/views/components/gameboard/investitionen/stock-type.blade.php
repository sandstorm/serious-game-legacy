@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\StockPriceState')
@use('Domain\CoreGameLogic\Feature\Spielzug\ValueObject\StockType')

@props([
    'stockType' => null,
    'gameEvents' => null,
])

<div class="stock-type">
    <div>
        <h4>{{ $stockType->toPrettyString() }}</h4>
        <div class="resource-change">{!! StockPriceState::getCurrentStockPrice($gameEvents, $stockType)->format() !!} / Aktie</div>
        <small>
            Handelsspanne:
            @if ($stockType === StockType::LOW_RISK)
                10 - 50 €
            @else
                20 - 100 €
            @endif
        </small>
    </div>
    <button
        type="button"
        @class([
            "button",
            "button--type-primary",
            "button--disabled" => !$this->canBuyStocks($stockType)->canExecute,
            $this->getPlayerColorClass(),
        ])
        wire:click="showBuyStocksOfType('{{ $stockType }}')"
    >
        wählen
    </button>
</div>
