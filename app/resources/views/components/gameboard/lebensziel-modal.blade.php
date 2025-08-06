@extends ('components.modal.modal', ['closeModal' => "closePlayerLebensziel()", "type" => "borderless"])

@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')

@props([
    'lebenszielDefinition' => null,
])

@section('title')
    <div><strong>Dein Lebensziel:</strong> {{ $lebenszielDefinition->name }}</div>
@endsection

@section('content')
    @if($lebenszielDefinition)
        <x-gameboard.lebensziel :lebensziel="$lebenszielDefinition" :player-id="$playerId" :game-events="$gameEvents" />
    @endif
@endsection
