@extends ('components.modal.modal', ['closeModal' => "toggleImmobilienModal()"])

@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\ImmobilienPriceState')

@props([
    'immobilienCards' => [],
    'immobilienOwnedByPlayer' => [],
])

@section('icon')
    <x-gameboard.phase-icon />
@endsection

@section('title')
    @if ($this->isBuyImmobilieVisible)
        <span>
            Kauf - Immobilie <i class="icon-immobilien" aria-hidden="true"></i>
        </span>
    @elseif ($this->isSellImmobilieVisible)
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
    @if ($this->isBuyImmobilieVisible)
        <x-gameboard.investitionen.investitionen-immobilien-buy :immobilien-cards="$immobilienCards" />
    @elseif ($this->isSellImmobilieVisible)
        <x-gameboard.investitionen.investitionen-immobilien-sell :immobilien-owned-by-player="$immobilienOwnedByPlayer" :game-events="$gameEvents" />
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
                        wire:click="showSellImmobilie()"
                    >
                        verkaufen
                    </button>
                </div>
            </div>
        </div>
    @endif
@endsection
