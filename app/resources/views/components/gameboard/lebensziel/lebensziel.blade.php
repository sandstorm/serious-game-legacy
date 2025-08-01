@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')

@props([
    'lebensziel' => null,
])

<div class="lebensziel">
    <strong>Lebensziel:</strong> {{ $lebensziel->name }}

    <div class="lebensziel__phasen">
        @foreach($lebensziel->phaseDefinitions as $phase)
            <div @class([
                    "lebensziel__phase",
                    "lebensziel__phase--inactive" => $phase->lebenszielPhaseId->value !== PlayerState::getCurrentLebenszielphaseIdForPlayer($gameEvents, $playerId)->value,
                ])
            >
                <h4>Phase {{$phase->lebenszielPhaseId->value}}</h4>
                <p>{{$phase->description}}</p>

                <div class="lebensziel__phase-kompetenzen">
                    <x-gameboard.lebensziel-kompetenzen :player-id="$playerId" :game-events="$gameEvents" :lebensziel-phase="$phase" />
                </div>

                @if ($phase->lebenszielPhaseId->value === PlayerState::getCurrentLebenszielphaseIdForPlayer($gameEvents, $playerId)->value)
                    <div class="lebensziel__phase-balance">
                        <small>Kontostand</small>
                        <div class="button button--type-primary">
                            {!! PlayerState::getGuthabenForPlayer($gameEvents, $playerId)->format() !!}
                        </div>
                    </div>
                @endif
                <div class="lebensziel__phase-cost">
                    <small>Phasenwechsel</small> <br />
                    <div>{!! $getCostForPhaseChange($phase->investitionen->value)->formatWithIcon() !!}</div>
                </div>

                @if($this->playerIsMyself($playerId) && $phase->lebenszielPhaseId->value >= PlayerState::getCurrentLebenszielphaseIdForPlayer($gameEvents, $playerId)->value)
                    <x-gameboard.lebensziel.lebensziel-switch :lebensziel-phase="$phase->lebenszielPhaseId->value" :current-phase="PlayerState::getCurrentLebenszielphaseIdForPlayer($gameEvents, $playerId)->value" />
                @endif
            </div>
        @endforeach
    </div>
</div>
