@extends ('components.modal.modal', ['closeModal' => "closeMinijob()", 'size' => 'medium'])

@section('title')
    Minijob
@endsection

@section('content')
    <div class="minijob">
        @if ($minijob)
            <h3>{{ $minijob->title }}</h3>
            {{$minijob->description}}
        <hr/>
            + {{ $minijob->resourceChanges->guthabenChange->value }}€
        @endif
    </div>
@endsection

@section('footer')
    <button type="button" class="button button--type-primary" wire:click="closeMinijob()">Schließen</button>
@endsection
