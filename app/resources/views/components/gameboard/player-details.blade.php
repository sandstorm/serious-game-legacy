@extends ('components.modal.modal', ['isVisible' => $getIsVisible()])
@section('title')
    Spieler Details
@endsection

@section('content')
    {{$playerDetails?->name}} ({{$playerDetails?->playerId->value}}) <br/>
    @if($playerDetails?->lebensziel)
        <x-lebensziel :lebensziel="$playerDetails?->lebensziel" />
    @endif
@endsection

@section('footer')
    <button type="button" class="button button--type-primary" wire:click="closePlayerDetails()">Schließen</button>
@endsection
