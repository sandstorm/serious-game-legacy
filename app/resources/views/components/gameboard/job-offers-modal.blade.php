@extends ('components.modal.modal', ['closeModal' => "closeJobOffer()", 'type' => "borderless"])
@use('Domain\CoreGameLogic\Feature\Spielzug\State\CurrentPlayerAccessor')

@props([
    'jobOffers' => [],
    'playerId' => null,
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
            <div @class(["card", "card--disabled" => !$this->canAcceptJobOffer($jobOffer->getId()->value)->canExecute])>
                <h4 class="card__title">{{ $jobOffer->getTitle() }}</h4>
                <div class="card__content">
                    <div class="resource-change">
                        {!! $jobOffer->getGehalt()->formatWithIcon() !!}
                    </div>
                    <span class="font-size--sm">Jahresgehalt brutto</span>
                    <button type="button"
                        @class([
                            "button",
                            "button--type-primary",
                            "button--disabled" => !$this->canAcceptJobOffer($jobOffer->getId()->value)->canExecute,
                            $this->getPlayerColorClass()
                        ])
                        wire:click="applyForJob('{{ $jobOffer->getId()->value }}')"
                    >
                        Das mache ich!
                    </button>
                </div>

                <div class="job-offer__requirements">
                    <h5>Voraussetzungen:</h5>
                    @if ($jobOffer->getRequirements()->bildungKompetenzsteine === 0 && $jobOffer->getRequirements()->freizeitKompetenzsteine === 0)
                        <strong>Keine</strong>
                    @else
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
            </div>
        @endforeach
    </div>
@endsection
