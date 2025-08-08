@extends ('components.modal.modal', ['closeModal' => "toggleImmobilienModal()"])

@props([
    'playerId' => null,
    'investitionCard' => null,
])

@section('icon')
    <x-gameboard.phase-icon />
@endsection

@section('title')
    <span>
        Kauf - Immobilien <i class="icon-immobilien" aria-hidden="true"></i>
    </span>
    <span class="font-size--base">
        Investitionen
    </span>
@endsection

@section('content')
    <div class="investitionen__buy-sell-immobilien">
        <div class="card-pile">
            <div class="shadow-card-1"></div>
            <div class="shadow-card-2"></div>
            <div @class(["card"])>
                <h4 class="card__title">{{ $investitionCard->getTitle() }}</h4>
                <small>{{ $investitionCard->getDescription() }}</small>
                <div class="card__content card__content--center">
                    <x-gameboard.resourceChanges.resource-changes :resource-changes="$investitionCard->getResourceChanges()" />

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
                        </button>
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
                        </button>
                    @endif
                </div>

                <div class="job-offer__requirements">
                    @if ($investitionCard->getAnnualRent()->value > 0)
                        <h5>Jährliche Miete:</h5>
                        {!! $investitionCard->getAnnualRent()->formatWithIcon() !!}
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
