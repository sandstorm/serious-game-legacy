@extends ('components.modal.modal', ['closeModal' => "closeKonjunkturzyklusDetails()"])
@section('title')
    {{ $konjunkturzyklus->type }}
@endsection

@section('content')
    <p>
        {{ $konjunkturzyklus->description }}
    </p>
    <p>
        {{ $konjunkturzyklus->additionalEvents }}
    </p>

    <h4>Verfügbare Kompetenzbereiche</h4>
    <ul>
        @foreach($konjunkturzyklus->kompetenzbereiche as $kompetenzbereich)
            <li>
                <strong>{{ $kompetenzbereich->name }}: </strong> {{ $kompetenzbereich->kompetenzsteine }}
            </li>
        @endforeach
    </ul>

    <h4>Auswirkungen</h4>
    <ul>
        @foreach($konjunkturzyklus->auswirkungen as $auswirkung)
            <li>
                <strong>{{ $auswirkung->scope }}: </strong> {{ $auswirkung->modifier }}
            </li>
        @endforeach
    </ul>
@endsection

@section('footer')
    <button type="button" class="button button--type-primary" wire:click="closeKonjunkturzyklusDetails()">Schließen</button>
@endsection
