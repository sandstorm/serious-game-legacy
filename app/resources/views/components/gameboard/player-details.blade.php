@extends ('components.modal.modal', ['closeModal' => "closePlayerDetails()",  'size' => 'medium'])
@section('title')
    Spieler Details
@endsection

@section('content')
    {{$playerDetails?->name}} ({{$playerDetails?->playerId->value}}) <br/>
    Phase: {{$playerDetails?->currentLebenszielPhase}} <br/>
    Guthaben: {{$playerDetails?->guthaben}}€ <br/>
    Zeitsteine: {{$playerDetails?->zeitsteine}} <br/>
    Kompetenzen Bildung & Karriere: {{$playerDetails?->kompetenzsteineBildung}} <br/>
    Kompetenzen Soziales & Freizeit: {{$playerDetails?->kompetenzsteineFreizeit}} <br/>
    <hr/>
    @if($playerDetails?->lebenszielDefinition)
        <x-lebensziel :lebensziel="$playerDetails?->lebenszielDefinition"/>
    @endif
    @if($isCurrentPlayer())
        <button
            type="button"
            @class([
            "button",
            "button--type-primary",
            "button--disabled" => !$this->canChangeLebenszielphase(),
            ])
            wire:click="changeLebenszielphase()">
            Phase wechseln
        </button>
    @endif
@endsection

@section('footer')
        <button type="button" class="button button--type-primary" wire:click="closePlayerDetails()">Schließen</button>
@endsection
