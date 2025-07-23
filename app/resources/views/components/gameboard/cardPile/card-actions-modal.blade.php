@extends ('components.modal.modal', ['closeModal' => "closeCardActions()", 'size' => 'medium'])

@props([
    'card' => null,
    'category' => null,
])

@section('icon')
    <i class="icon-phase1"></i>
@endsection

@section('title')
    {{ $card->title }} -  {{ $category }}
@endsection

@section('content')
    <p>
        {{ $card->description }}
    </p>
@endsection

@section('footer')
    <div class="card__actions-footer">
        <x-gameboard.cardPile.card-effects style-class="horizontal" :card="$card" />

        <button
            type="button"
            @class([
                "button",
                "button--type-outline-primary",
                "button--disabled" => !$this->canSkipCard($category)->canExecute,
            ])
            wire:click="skipCard('{{$category}}')"
        >
            <i class="icon-skippen" aria-hidden="true"></i> Karte skippen
        </button>
        <button
            type="button"
            @class([
               "button",
               "button--type-primary",
               "button--disabled" => !$this->canActivateCard($category)->canExecute,
           ])
            wire:click="activateCard('{{$category}}')"
        >
            Karte spielen
        </button>
    </div>
@endsection
