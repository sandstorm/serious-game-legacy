@extends ('components.modal.modal', ['closeModal' => "toggleImmobilienModal()"])

@props([
    'playerId' => null,
    'immobilien' => [],
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
    <div class="job-offers">
        @foreach($immobilien as $immobilie)
            <div @class(["card", "card--disabled" => !$this->canBuyImmobilie($immobilie->getId())->canExecute])>
                <h4 class="card__title">{{ $immobilie->getTitle() }}</h4>
                <div class="card__content">
                    <x-gameboard.resourceChanges.resource-changes :resource-changes="$immobilie->getResourceChanges()" />
                    <button type="button"
                        @class([
                            "button",
                            "button--type-primary",
                            "button--disabled" => !$this->canBuyImmobilie($immobilie->getId())->canExecute,
                            $this->getPlayerColorClass()
                        ])
                        wire:click="buyImmobilie('{{ $immobilie->getId()->value }}')"
                    >
                        Diese Immobilie kaufen
                    </button>
                </div>

                <div class="job-offer__requirements">
                    <h5>Jährliche Miete:</h5>
                    {!! $immobilie->getAnnualRent()->formatWithIcon() !!}

                </div>
            </div>
        @endforeach
    </div>
@endsection
