@extends ('components.modal.modal', ['closeModal' => "closeJobOffer()"])

@section('title')
    {{$title}}
@endsection

@section('content')
    <p>{{$description}}</p>
    <hr/>
    <ul>
        @if ($resourceChanges->guthabenChange->value > 0)
            <li>Guthaben: {!! $resourceChanges->guthabenChange->format() !!}</li>
        @endif
        @if ($resourceChanges->zeitsteineChange)
            <li>Zeitstein: {{ $resourceChanges->zeitsteineChange}}</li>
        @endif
        @if ($resourceChanges->bildungKompetenzsteinChange)
            <li>Bildung: {{ $resourceChanges->bildungKompetenzsteinChange}}</li>
        @endif
        @if ($resourceChanges->freizeitKompetenzsteinChange)
            <li>Freizeit: {{ $resourceChanges->freizeitKompetenzsteinChange}}</li>
        @endif
    </ul>
@endsection

@section('footer')
    <button type="button" class="button button--type-primary" wire:click="closeEreignisCard()">Schließen</button>
@endsection
