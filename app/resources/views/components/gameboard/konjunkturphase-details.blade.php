@extends ('components.modal.modal', ['closeModal' => "closeKonjunkturphaseDetails()",  'size' => 'medium'])
@use('Domain\CoreGameLogic\Feature\Initialization\State\PreGameState')

@section('title')
    {{ $konjunkturphase->type }}
@endsection

@section('content')
    <p>
        {{ $konjunkturphase->description }}
    </p>
    <p>
        {{ $konjunkturphase->additionalEvents }}
    </p>

    <h4>Verfügbare Kompetenzbereiche</h4>
    <ul>
        @foreach($konjunkturphase->kompetenzbereiche as $kompetenzbereich)
            <li>
                <strong>{{ $kompetenzbereich->name }}
                    : </strong> {{ $kompetenzbereich->zeitslots->getAmountOfZeitslotsForPlayer(PreGameState::getAmountOfPlayers($gameEvents)) }}
            </li>
        @endforeach
    </ul>

    <h4>Auswirkungen</h4>
    <ul>
        @foreach($konjunkturphase->auswirkungen as $auswirkung)
            <li>
                <strong>{{ $auswirkung->scope }}: </strong> {{ $auswirkung->modifier }}
            </li>
        @endforeach
    </ul>
@endsection

@section('footer')
    <button type="button" class="button button--type-primary" wire:click="closeKonjunkturphaseDetails()">Schließen
    </button>
@endsection
