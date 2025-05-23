@extends ('components.modal.modal', ['closeModal' => "closePlayerDetails()"])
@section('title')
    Spieler Details
@endsection

@section('content')
    {{$playerDetails?->name}} ({{$playerDetails?->playerId->value}}) <br/>
    Guthaben: {{$playerDetails?->guthaben}}€ <br/>
    Zeitsteine: {{$playerDetails?->zeitsteine}} <br/>
    Kompetenzen Bildung & Karriere: {{$playerDetails?->kompetenzsteineBildung}} <br/>
    Kompetenzen Soziales & Freizeit: {{$playerDetails?->kompetenzsteineFreizeit}} <br/>
    <hr />
    @if($playerDetails?->lebensziel)
        <x-lebensziel :lebensziel="$playerDetails?->lebensziel" />
    @endif
@endsection

@section('footer')
    <button type="button" class="button button--type-primary" wire:click="closePlayerDetails()">Schließen</button>
@endsection
