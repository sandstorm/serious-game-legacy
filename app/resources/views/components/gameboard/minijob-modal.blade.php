@extends ('components.modal.modal', ['closeModal' => "closeMinijob()", 'size' => 'medium'])

@props([
    'minijob' => null,
    'resourceChanges' => null,
])

@section('icon')
    <x-gameboard.phase-icon />
@endsection

@section('title')
    <div class="card__actions-header">
        <div>
            {{ $minijob?->title }}
        </div>
        <div class="card__actions-header-category">
            Minijob
        </div>
    </div>
@endsection

@section('content')
    <p>
        {{ $minijob?->description }}
    </p>

    @if ($resourceChanges)
        <x-gameboard.cardPile.card-effects :resource-changes="$resourceChanges" style-class="horizontal" />
    @endif
@endsection

@section('footer')
    <button
        type="button"
        @class([
           "button",
           "button--type-primary",
           $this->getButtonPlayerClass(),
        ])
        wire:click="closeMinijob()"
    >
        Akzeptieren
    </button>
@endsection
