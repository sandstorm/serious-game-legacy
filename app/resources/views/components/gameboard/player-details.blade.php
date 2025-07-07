@extends ('components.modal.modal', ['closeModal' => "closePlayerDetails()",  'size' => 'medium'])
@section('title')
    Spieler Details
@endsection

@section('content')
    {{$playerDetails?->name}} ({{$playerDetails?->playerId->value}}) <br/>
    Guthaben: {{$playerDetails?->guthaben}}€ <br/>
    Zeitsteine: {{$playerDetails?->zeitsteine}} <br/>
    Kompetenzen Bildung & Karriere: {{$playerDetails?->kompetenzsteineBildung}} <br/>
    Kompetenzen Soziales & Freizeit: {{$playerDetails?->kompetenzsteineFreizeit}} <br/>
    <hr/>
    @if($playerDetails?->lebensziel)
        <x-lebensziel :lebensziel="$playerDetails?->lebensziel"/>
    @endif
    <button type="button" class="button button--type-primary" wire:click="changeLebenszielphase()">Phase wechseln</button>
@endsection

@section('footer')
        <button type="button" class="button button--type-primary" wire:click="closePlayerDetails()">Schließen</button>
@endsection
