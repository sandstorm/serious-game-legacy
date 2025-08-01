@extends ('components.modal.modal', ['closeModal' => "closeCardActions()", 'size' => 'medium'])

@use('\Domain\Definitions\Konjunkturphase\ValueObject\CategoryId')

@props([
    'card' => null,
    'category' => null,
    'pileId' => null,
])

@section('icon')
    <x-gameboard.phase-icon />
@endsection

@section('title')
    <div class="card__actions-header">
        <div>
            {{ $card->getTitle() }}
        </div>
        <div class="card__actions-header-category">
            @if ($category === CategoryId::SOZIALES_UND_FREIZEIT->value)
                <i class="icon-freizeit-und-soziales"></i>
            @endif
            @if ($category === CategoryId::BILDUNG_UND_KARRIERE->value)
                <i class="icon-bildung-und-karriere"></i>
            @endif
            {{ $category }}
        </div>
    </div>
@endsection

@section('content')
    <p>
        {{ $card->getDescription() }}
    </p>

    @if ($this->playerHasToPlayCard)
        <p class="text--danger">
            Du hast eine Karte geskippt und musst diese Karte jetzt spielen.
            Wenn du die Karte nicht spielen kannst, musst du sie zurück legen.
        </p>
    @endif
@endsection

@section('footer')
    <div class="card__actions-footer">
        <x-gameboard.resourceChanges.resource-changes style-class="horizontal" :resource-changes="$card->getResourceChanges()" />

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

        <button
            type="button"
            @class([
               "button",
               "button--type-primary",
               "button--disabled" => !$this->canActivateCard($category)->canExecute,
               $this->getPlayerColorClass(),
            ])
            wire:click="activateCard('{{$category}}')"
        >
            Karte spielen
            @if (!$this->playerHasToPlayCard)
                <div class="button__suffix">
                    <div>
                        <i class="icon-minus text--danger" aria-hidden="true"></i><i class="icon-zeitstein" aria-hidden="true"></i>
                        <span class="sr-only">, kostet einen Zeitstein</span>
                    </div>
                </div>
            @endif
        </button>

        @if ($this->playerHasToPlayCard && !$this->canActivateCard($category)->canExecute)
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
    </div>
@endsection
