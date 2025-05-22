@extends ('components.modal.modal', ['isVisible' => $getIsVisible(), 'closeModal' => "closePlayerDetails()"])
@section('title')
    Spieler Details
@endsection

@section('content')
    {{$playerDetails?->name}} ({{$playerDetails?->playerId->value}}) <br/>
    Guthaben: {{$playerDetails?->guthaben}}€
    <hr />
    @if($playerDetails?->lebensziel)
        <x-lebensziel :lebensziel="$playerDetails?->lebensziel" />
    @endif
@endsection

@section('footer')
    <button type="button" class="button button--type-primary" wire:click="closePlayerDetails()">Schließen</button>
@endsection
