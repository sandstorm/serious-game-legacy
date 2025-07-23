@extends ('components.modal.modal', ['closeModal' => "toggleCardActionsModal()", 'size' => 'medium'])

@props([
    'card' => null,
    'category' => null,
])

@section('title')
    {{ $card->title }}
@endsection

@section('content')
    {{ $card->description }}

    <button
        type="button"
        @class([
            "button",
            "button--type-outline-primary",
            "button--disabled" => !$this->canSkipCard($category)->canExecute,
        ])
        wire:click="skipCard('{{$category}}')"
    >
        Karte skippen
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
@endsection

@section('footer')
    <button type="button" class="button button--type-primary" wire:click="toggleCardActionsModal()">Schließen</button>
@endsection
