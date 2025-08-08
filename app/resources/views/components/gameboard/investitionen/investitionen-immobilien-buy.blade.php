@props([
    'immobilienCards' => [],
])

<div class="immoblien">
    @foreach($immobilienCards as $immobilieCard)
        <div @class(["card", "card--disabled" => !$this->canBuyImmobilie($immobilieCard->getId())->canExecute])>
            <h4 class="card__title">{{ $immobilieCard->getTitle() }}</h4>
            <div class="card__content card__content--center">
                <div class="resource-changes">
                    <div class="resource-change">
                        {!! $immobilieCard->getAnnualRent()->formatWithIcon() !!}
                    </div>
                </div>
                <span class="font-size--sm">Jährliche Miete</span>
                <x-gameboard.resourceChanges.resource-changes :resource-changes="$immobilieCard->getResourceChanges()" />
                <span class="font-size--sm">Kaufpreis</span>

                <button type="button"
                    @class([
                        "button",
                        "button--type-primary",
                        "button--disabled" => !$this->canBuyImmobilie($immobilieCard->getId())->canExecute,
                        $this->getPlayerColorClass()
                    ])
                    wire:click="buyImmobilie('{{ $immobilieCard->getId()->value }}')"
                >
                    Kaufen
                    <div class="button__suffix">
                        <div>
                            <i class="icon-minus text--danger" aria-hidden="true"></i><i class="icon-zeitstein" aria-hidden="true"></i>
                            <span class="sr-only">, kostet einen Zeitstein</span>
                        </div>
                    </div>
                </button>
            </div>
        </div>
    @endforeach
</div>
