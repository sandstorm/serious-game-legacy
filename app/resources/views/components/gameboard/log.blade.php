@extends ('components.modal.modal', ['closeModal' => "closeLog()"])

@section('title')
    Log
@endsection

@section('content')
<div class="log">
    <ul>
        @foreach($this->getPrettyEvents() as $event)
            <li class="log__event">
                <h2>{{$event['eventClass']}}</h2>
                <ul>
                    @foreach($event as $key => $item)
                        <li class="log__event-item"><strong>{{$key}}:</strong> {{$item}}</li>
                    @endforeach
                </ul>
            </li>
        @endforeach
    </ul>
</div>
@endsection

@section('footer')
    <button type="button" class="button button--type-secondary" wire:click="closeLog()">Schließen</button>
@endsection
