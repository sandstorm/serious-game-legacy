@extends ('components.modal.modal', ['closeModal' => "closeKonjunkturphaseDetails()"])
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
                    : </strong> {{ $kompetenzbereich->zeitslots->getAmountOfZeitslotsForPlayerCount(PreGameState::getAmountOfPlayers($gameEvents)) }}
            </li>
        @endforeach
    </ul>

    <h4>Auswirkungen</h4>
    <ul>
        @foreach($konjunkturphase->getDisplayedAuswirkungen() as $auswirkung)
            <li>
                <strong>{{ $auswirkung->label }}: </strong> <x-formatted-number :value="$auswirkung->value" :suffix="$auswirkung->unit" />
            </li>
        @endforeach
        @foreach($konjunkturphase->getDisplayedAuswirkungDescriptions() as $auswirkungDescription)
            <li>{{ $auswirkungDescription }}</li>
        @endforeach
    </ul>
@endsection

@section('footer')
    <button type="button" class="button button--type-secondary" wire:click="closeKonjunkturphaseDetails()">
        Schließen
    </button>
@endsection
