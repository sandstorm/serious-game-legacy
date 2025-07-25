@extends ('components.modal.modal', ['closeModal' => "closeJobOffer()", 'size' => 'medium'])
@use('Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator')

@props([
    'jobOffers' => [],
    'playerId' => null,
])

@section('title')
    Jobbörse - Ein Job kostet Zeit. Pro Jahr bleibt dir ein Zeitstein weniger. - <i class="icon-bildung-und-karriere"></i> Beruf
@endsection

@section('icon')
    <x-gameboard.phase-icon />
@endsection

@section('content')
    <div class="job-offers">
        @foreach($jobOffers as $jobOffer)
            <div @class(["card", "card--disabled" => !AktionsCalculator::forStream($gameEvents)->canPlayerAffordJobCard($playerId, $jobOffer)])>
                <h4 class="card__title">{{ $jobOffer->title }}</h4>
                <div class="card__content">
                    <div class="card__effect">
                        {!! $jobOffer->gehalt->formatWithIcon() !!} <br />
                    </div>
                    <span class="font-size--sm">Jahresgehalt brutto</span>
                    <button type="button"
                        @class([
                            "button",
                            "button--type-primary",
                            "button--disabled" => !AktionsCalculator::forStream($gameEvents)->canPlayerAffordJobCard($playerId, $jobOffer),
                            $this->getButtonPlayerClass()
                        ])
                        wire:click="applyForJob('{{ $jobOffer->id->value }}')"
                    >
                        Das mache ich!
                    </button>
                </div>

                <div class="job-offer__requirements">
                    <h5>Voraussetzungen:</h5>
                    @if ($jobOffer->requirements->bildungKompetenzsteine === 0 && $jobOffer->requirements->freizeitKompetenzsteine === 0)
                        <strong>Keine</strong>
                    @else
                        <ul class="kompetenzen">
                            @for($i = 0; $i < $jobOffer->requirements->bildungKompetenzsteine; $i++)
                                <x-gameboard.kompetenzen.kompetenz-icon-bildung
                                    :drawEmpty="true"
                                />
                            @endfor
                            @for($i = 0; $i < $jobOffer->requirements->freizeitKompetenzsteine; $i++)
                                <x-gameboard.kompetenzen.kompetenz-icon-freizeit
                                    :drawEmpty="true"
                                />
                            @endfor
                        </ul>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endsection

@section('footer')
@endsection
