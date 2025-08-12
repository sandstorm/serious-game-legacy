@extends ('components.modal.modal', ['closeModal' => "toggleImmobilienModal()"])

@props([
    'playerId' => null,
    'investitionCard' => null,
    'category' => null,
    'pileId' => null,
])

@section('icon')
    <x-gameboard.phase-icon />
@endsection

@section('title')
    <div class="card__actions-header">
        <div>
            {{ $investitionCard->getTitle() }}
        </div>
        <div class="card__actions-header-category">
            <i class="icon-immobilien" aria-hidden="true"></i>
            Investitionen
        </div>
    </div>
@endsection

@section('content')
    <p>
        {{ $investitionCard->getDescription() }}
    </p>

    @if ($investitionCard->getAnnualRent()->value > 0)
        <strong>Jährliche Miete:</strong> <br />
        {!! $investitionCard->getAnnualRent()->formatWithIcon() !!}
    @endif

    @if ($this->playerHasToPlayCard)
        <p class="text--danger">
            Du hast eine Karte geskippt und musst diese Karte jetzt spielen.
            Wenn du die Karte nicht spielen kannst, musst du sie zurück legen.
        </p>
    @endif
@endsection

@section('footer')
    <div class="card__actions-footer">
        <x-gameboard.resourceChanges.resource-changes style-class="horizontal" :resource-changes="$investitionCard->getResourceChanges()" />

        @if (!$this->playerHasToPlayCard)
            <button
                type="button"
                @class([
                    "button",
                    "button--type-outline-primary",
                    "button--disabled" => !$this->canSkipCard($category)->canExecute,
                    $this->getPlayerColorClass(),
                ])
                wire:click="skipCard('{{$category}}', '{{$pileId}}')"
            >
                <i class="icon-skippen" aria-hidden="true"></i>
                Karte skippen
                <div class="button__suffix">
                    <div>
                        <i class="icon-minus text--danger" aria-hidden="true"></i><i class="icon-zeitstein" aria-hidden="true"></i>
                        <span class="sr-only">, kostet einen Zeitstein</span>
                    </div>
                </div>
            </button>
        @endif

        @if ($investitionCard->getResourceChanges()->guthabenChange->value > 0)
            <button type="button"
                    @class([
                        "button",
                        "button--type-primary",
                        "button--disabled" => !$this->canSellImmobilie($investitionCard->getId())->canExecute,
                        $this->getPlayerColorClass()
                    ])
                    wire:click="sellImmobilie('{{ $investitionCard->getId()->value }}')"
            >
                Immobilie verkaufen
                @if (!$this->playerHasToPlayCard)
                    <div class="button__suffix">
                        <div>
                            <i class="icon-minus text--danger" aria-hidden="true"></i><i class="icon-zeitstein" aria-hidden="true"></i>
                            <span class="sr-only">, kostet einen Zeitstein</span>
                        </div>
                    </div>
                @endif
            </button>
            @if ($this->playerHasToPlayCard && !$this->canSellImmobilie($investitionCard->getId())->canExecute)
                <button
                    type="button"
                    @class([
                       "button",
                       "button--type-primary",
                       $this->getPlayerColorClass(),
                    ])
                    wire:click="putCardBackOnTopOfPile('{{$category}}')"
                >
                    Karte zurück legen
                </button>
            @endif
        @else
            <button type="button"
                    @class([
                        "button",
                        "button--type-primary",
                        "button--disabled" => !$this->canBuyImmobilie($investitionCard->getId())->canExecute,
                        $this->getPlayerColorClass()
                    ])
                    wire:click="buyImmobilie('{{ $investitionCard->getId()->value }}')"
            >
                Immobilie kaufen
                @if (!$this->playerHasToPlayCard)
                    <div class="button__suffix">
                        <div>
                            <i class="icon-minus text--danger" aria-hidden="true"></i><i class="icon-zeitstein" aria-hidden="true"></i>
                            <span class="sr-only">, kostet einen Zeitstein</span>
                        </div>
                    </div>
                @endif
            </button>
            @if ($this->playerHasToPlayCard && !$this->canBuyImmobilie($investitionCard->getId())->canExecute)
                <button
                    type="button"
                    @class([
                       "button",
                       "button--type-primary",
                       $this->getPlayerColorClass(),
                    ])
                    wire:click="putCardBackOnTopOfPile('{{$category}}')"
                >
                    Karte zurück legen
                </button>
            @endif
        @endif
    </div>
@endsection
