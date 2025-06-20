@extends ('components.modal.modal', ['closeModal' => "closeJobOffer()", 'size' => 'medium'])
@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')
@use('Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator')

@section('title')
    Jobangebote
@endsection

@section('content')
    <h2>Jobangebote</h2>
    <p>Hier kannst du dir die Jobangebote anschauen, die dir zur Verfügung stehen.</p>
    <div class="job-offers">
        @foreach($jobOffers as $jobOffer)
            <div class="job-offers__item">
                <h3>{{ $jobOffer->title }}</h3>
                {{ $jobOffer->description }}
                <hr/>
                + {{ $jobOffer->gehalt->value }}€ p.a. <br/>

                <hr/>
                <div class="job-offers__item-requirements">
                    <h4>Voraussetzungen:</h4>
                    <ul class="requirements">
                        @if($jobOffer->requirements->bildungKompetenzsteine > 0)
                            <li class="requirements__item">Bildung und
                                Karriere: {{$jobOffer->requirements->bildungKompetenzsteine}}</li>
                        @endif
                        @if($jobOffer->requirements->freizeitKompetenzsteine > 0)
                            <li class="requirements__item">Soziales und
                                Freizeit: {{$jobOffer->requirements->freizeitKompetenzsteine}}</li>
                        @endif
                    </ul>
                </div>

                <hr/>
                <button type="button"
                        @class([
                                "button",
                                "button--type-primary",
                                "button--disabled" => !AktionsCalculator::forStream($gameEvents)->canPlayerAffordJobCard($playerId, $jobOffer),
                                ])
                        wire:click="applyForJob('{{ $jobOffer->id->value }}')">
                    Das mache ich!
                </button>
            </div>
        @endforeach
    </div>
@endsection

@section('footer')
    {{-- TODO Warn player that the Zeitstein will be used anyway? (vs. opening the modal again this turn shows same jobs at no cost) --}}
    <button type="button" class="button button--type-primary" wire:click="closeJobOffer()">Schließen</button>
@endsection
