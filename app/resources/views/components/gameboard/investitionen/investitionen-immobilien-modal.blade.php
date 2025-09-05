@extends ('components.modal.modal', ['closeModal' => "toggleImmobilienModal()"])

@props([
    'playerId' => null,
    'investitionCard' => null,
    'pileId' => null,
])

@section('icon')
    <x-gameboard.phase-icon />
@endsection

@section('title')
    @if ($this->buyImmobilieIsVisible)
        <span>
            Kauf - Immobilie <i class="icon-immobilien" aria-hidden="true"></i>
        </span>
    @elseif ($this->sellInvestmentOfType)
        <span>
            Verkauf - Immobilie <i class="icon-immobilien" aria-hidden="true"></i>
        </span>
    @else
        <span>
            Immobilien <i class="icon-immobilien" aria-hidden="true"></i>
        </span>
        <span class="font-size--base">
            Investitionen
        </span>
    @endif
@endsection

@section('content')
    @if ($this->buyImmobilieIsVisible)
        <h3>
            {{ $investitionCard->getTitle() }}
        </h3>

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
    @else
        <p>
            Sachwerte wie Wohn- oder Gewerbegebäude, die in erster Linie
            Erträge durch Mieteinnahmen und potenziellen Wertzuwachs bieten. Sie gelten
            als vergleichsweise inflationsgeschützt, sind aber illiquide, kapitalintensiv und
            anfällig für Standort- sowie Zinsrisiken.
        </p>
        <div class="investitionen-types">
            <div class="investitionen-type">
                <div>
                    <h4>Immobilie</h4>
                </div>
                <div class="investitionen-type__actions">
                    <button
                        type="button"
                        @class([
                            "button",
                            "button--type-primary",
                            $this->getPlayerColorClass(),
                        ])
                        wire:click="showBuyImmobilie()"
                    >
                        kaufen
                    </button>
                    <button
                        type="button"
                        @class([
                            "button",
                            "button--type-outline-primary",
                            $this->getPlayerColorClass(),
                        ])
                    >
                        verkaufen
                    </button>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('footer')
    @if ($this->buyImmobilieIsVisible)
        <div class="card__actions-footer">
            <x-gameboard.resourceChanges.resource-changes style-class="horizontal" :resource-changes="$investitionCard->getResourceChanges()" />

            @if (!$this->playerHasToPlayCard)
                <button
                    type="button"
                    @class([
                        "button",
                        "button--type-outline-primary",
                        "button--disabled" => !$this->canSkipCard($investitionCard->getCategory()->value)->canExecute,
                        $this->getPlayerColorClass(),
                    ])
                    wire:click="skipCard('{{$investitionCard->getCategory()->value}}', '{{$pileId}}')"
                >
                    <i class="icon-skippen" aria-hidden="true"></i>
                    Nächste Immobilie anschauen
                    <div class="button__suffix">
                        <div>
                            <i class="icon-minus text--danger" aria-hidden="true"></i><i class="icon-zeitstein" aria-hidden="true"></i>
                            <span class="sr-only">, kostet einen Zeitstein</span>
                        </div>
                    </div>
                </button>
            @endif

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
                    wire:click="putCardBackOnTopOfPile('{{$investitionCard->getCategory()->value}}')"
                >
                    Immobilie nicht kaufen
                </button>
            @endif
        </div>
    @endif
@endsection
