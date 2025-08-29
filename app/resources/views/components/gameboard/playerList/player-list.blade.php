@props([
    'players' => [],
    'emptySlots' => [],
])

<div x-data="playerList()" x-trap.noscroll="playerListOpen" @touchstart.passive="touchStart($event)" @touchend.passive="touchEnd()"
    @class([
        'player-list',
    ])
    :class="playerListOpen ? 'player-list--show-details' : ''"
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
            <button type="button" title="Spielerübersicht öffnen/schließen" class="button button--type-borderless" x-on:click="playerListOpen = !playerListOpen">
                @if ($player->isPlayersTurn)
                    <div class="player-list__player-turn-indicator" aria-hidden="true"></div>
                    <span class="sr-only">Aktueller Spieler</span>
                @endif

                <div class="player-list__player-name">
                    {{ $player->name }}
                </div>

                <ul class="zeitsteine">
                    @foreach($player->zeitsteine as $playerZeitstein)
                        <x-gameboard.zeitsteine.zeitstein-icon :player-name="$player->name" :player-color-class="$playerZeitstein->colorClass" :draw-empty="$playerZeitstein->drawEmpty" />
                    @endforeach
                </ul>

                <div class="player-list__player-phase">
                    <x-gameboard.phase-icon :player-id="$player->playerId"/>
                </div>
            </button>

            <div class="player-list__player-details" x-cloak x-show="playerListOpen">
                <small><a href={{ @route("game-play.game", ['gameId' => $this->gameId, 'playerId' => $player->playerId]) }}>{{ $player->playerId }}</a></small>
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

                <button type="button"
                    @class([
                        'button',
                        'button--type-primary',
                        'player-list__close-button',
                        $player->playerColorClass,
                    ])
                    x-on:click="playerListOpen = false"
                >
                    <i class="icon-arrow-up" aria-hidden="true"></i>
                    <span class="sr-only">Spielerübersicht schließen</span>
                </button>
            </div>
        </div>
    @endforeach
</div>
