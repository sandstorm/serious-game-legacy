@props([
    'players' => [],
    'emptySlots' => [],
])

<div x-data="{ open: @entangle('showPlayerDetails') }" x-trap.noscroll="open"
    @class([
        'player-list',
        'player-list--show-details' => $this->showPlayerDetails,
    ])
>
    @foreach($emptySlots as $emptySlot)
        <li
            @class([
                'player-list__player',
                $emptySlot->playerColorClass,
            ])>
        </li>
    @endforeach

    @foreach($players as $player)
        <div
            @class([
                'player-list__player',
                'player-list__player--is-active' => $player->isPlayersTurn,
                $player->playerColorClass,
            ])
        >
            <button type="button" title="Spielerübersicht öffnen/schließen" class="button button--type-borderless" wire:click="togglePlayerDetails()">
                <div class="player-list__player-name">
                    {{ $player->name }}
                </div>

                <ul class="zeitsteine">
                    @foreach($player->zeitsteine as $playerZeitstein)
                        <x-gameboard.zeitsteine.zeitstein-icon :player-name="$player->name" :player-color-class="$playerZeitstein->colorClass" :draw-empty="$playerZeitstein->drawEmpty" />
                    @endforeach
                </ul>

                <div class="player-list__player-phase">
                    <x-gameboard.phase-icon />
                </div>
            </button>

            @if ($this->showPlayerDetails)
                <div class="player-list__player-details">
                    <small>{{ $player->playerId }}</small>
                    <div>
                        <strong>Lebensziel:</strong> {{ $player->lebenszielDefinition->name }}
                    </div>
                    <x-gameboard.lebensziel-kompetenzen :player-id="$player->playerId" :game-events="$gameEvents" :lebensziel-phase="$player->phaseDefinition" />

                    @if ($player->job)
                        <div class="player-list__player-details-job">
                            <x-gameboard.kompetenzen.kompetenz-icon-beruf
                                :player-color-class="$player->playerColorClass"
                                :player-name="$player->name"
                                :draw-empty="false"
                            />
                            <div>
                                {{ $player->job->getTitle() }} <br />
                                {!! $player->gehalt->format() !!} p.a.
                            </div>
                        </div>
                    @endif

                    @if ($player->sumOfLoans->value > 0)
                        <div>
                            Kreditsumme {!! $player->sumOfLoans->format() !!}
                        </div>
                    @endif
                    @if ($player->sumOfInvestments->value > 0)
                        <div>
                            Summe Investitionen {!! $player->sumOfInvestments->format() !!}
                        </div>
                    @endif

                    <div class="player-list__player-details-footer">
                        @if($this->playerIsMyself($player->playerId))
                            <x-gameboard.lebensziel.lebensziel-switch :lebensziel-phase="$player->phaseDefinition->lebenszielPhaseId->value" :current-phase="$player->phaseDefinition->lebenszielPhaseId->value" />
                        @endif
                        <div>
                            <i class="icon-phasenwechsel" aria-hidden="true"></i> {!! $player->phaseDefinition->investitionen->format() !!}
                        </div>
                        <div class="player-list__player-details-guthaben">
                            {!! $player->guthaben->format() !!}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endforeach
</div>
