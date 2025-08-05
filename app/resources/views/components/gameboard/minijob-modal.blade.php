@extends ('components.modal.modal', ['closeModal' => "closeMinijob()"])

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
            {{ $minijob?->getTitle() }}
        </div>
        <div class="card__actions-header-category">
            Minijob
        </div>
    </div>
@endsection

@section('content')
    <p>
        {{ $minijob?->getDescription() }}
    </p>

    @if ($resourceChanges)
        <x-gameboard.resourceChanges.resource-changes :resource-changes="$resourceChanges" style-class="horizontal" />
    @endif
@endsection

@section('footer')
    <button
        type="button"
        @class([
           "button",
           "button--type-primary",
           $this->getPlayerColorClass(),
        ])
        wire:click="closeMinijob()"
    >
        Akzeptieren
    </button>
@endsection
