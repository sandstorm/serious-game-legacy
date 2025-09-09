@extends ('components.modal.modal', ['closeModal' => "closeJobOffer()", 'type' => "borderless"])

@use('Domain\CoreGameLogic\Feature\Spielzug\State\CurrentPlayerAccessor')
@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')

@props([
    'jobOffers' => [],
    'playerId' => null,
    'gameEvents' => null,
])

@section('title')
    <div class="job-offers__header">
        <span>Jobbörse</span>
        <div class="job-offers__header-info">
            <x-gameboard.resourceChanges.resource-change sr-label="Zeitsteine" change="-1" iconClass="icon-zeitstein" />
            Ein Job kostet Zeit. Pro Jahr bleibt dir ein Zeitstein weniger.
        </div>
        <div class="job-offers__header-category">
            <i class="icon-erwerbseinkommen" aria-hidden="true"></i> Beruf
        </div>
    </div>
@endsection

@section('icon')
    <x-gameboard.phase-icon />
@endsection

@section('content')
    <div class="job-offers">
        @foreach($jobOffers as $jobOffer)
            <button type="button" wire:click="applyForJob('{{ $jobOffer->getId()->value }}')" title="Beruf annehmen" @class(["card", "card--disabled" => !$this->canAcceptJobOffer($jobOffer->getId()->value)->canExecute])>
                <h4 class="card__title">{{ $jobOffer->getTitle() }}</h4>
                <div class="card__content">
                    <div class="resource-change">
                        {!! $jobOffer->getGehalt()->formatWithIcon() !!}
                    </div>
                    <span class="font-size--sm">Jahresgehalt brutto</span>
                    <span
                        aria-hidden="true"
                        @class([
                            "button",
                            "button--type-primary",
                            "button--disabled" => !$this->canAcceptJobOffer($jobOffer->getId()->value)->canExecute,
                            $this->getPlayerColorClass()
                        ])
                    >
                        Das mache ich!
                    </span>
                </div>

                <div class="job-offer__requirements">
                    <h5>Voraussetzungen:</h5>
                    @if ($jobOffer->getRequirements()->bildungKompetenzsteine === 0 && $jobOffer->getRequirements()->freizeitKompetenzsteine === 0)
                        <strong>Keine</strong>
                    @else
                        <span class="sr-only">Kompetenzsteine im Bereich Bildung & Karriere: {{ $jobOffer->getRequirements()->bildungKompetenzsteine  }}</span>
                        <span class="sr-only">Kompetenzsteine im Bereich Freizeit & Soziales: {{ $jobOffer->getRequirements()->freizeitKompetenzsteine  }}</span>
                        <ul class="kompetenzen">
                            @for($i = 0; $i < $jobOffer->getRequirements()->bildungKompetenzsteine; $i++)
                                <x-gameboard.kompetenzen.kompetenz-icon-bildung
                                    :drawEmpty="true"
                                />
                            @endfor
                            @for($i = 0; $i < $jobOffer->getRequirements()->freizeitKompetenzsteine; $i++)
                                <x-gameboard.kompetenzen.kompetenz-icon-freizeit
                                    :drawEmpty="true"
                                />
                            @endfor
                        </ul>
                    @endif
                </div>
            </button>
        @endforeach
    </div>
    <div class="job-offers__kompetenzen">
        <div class="badge-with-background">
            <span>Deine bisher erworbenen Kompetenzen: </span>
            <x-gameboard.lebensziel-kompetenzen
                :player-id="$playerId"
                :game-events="$gameEvents"
                :lebensziel-phase="PlayerState::getCurrentLebenszielphaseDefinitionForPlayer($gameEvents, $playerId)"
            />
        </div>
    </div>
@endsection
