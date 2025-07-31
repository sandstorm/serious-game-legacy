@extends ('components.modal.modal', ['closeModal' => "closePlayerDetails()",  'size' => 'large'])

@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')

@props([
    'lebensziel' => null,
])

@props([
    'lebenszielDefinition' => null,
])

@section('title')
    {{ PlayerState::getNameForPlayer($gameEvents, $playerId) }}
@endsection

@section('icon')
    <x-gameboard.phase-icon />
@endsection

@section('content')
    <small>{{ $playerId->value }}</small>

    @if($lebenszielDefinition)
        <x-gameboard.lebensziel :lebensziel="$lebenszielDefinition" :player-id="$playerId" :game-events="$gameEvents" />
    @endif
@endsection

@section('footer')
    <button type="button" class="button button--type-primary" wire:click="closePlayerDetails()">Schließen</button>
@endsection
