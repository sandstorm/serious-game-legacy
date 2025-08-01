@use('Domain\CoreGameLogic\Feature\Initialization\State\PreGameState')
@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')

@props([
    'players' => [],
    'emptySlots' => [],
])

<div @class([
    "player-list",
    'player-list--show-details' => $this->showPlayerDetails,
])>
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
            ])>

            <button type="button" title="Spielerübersicht öffnen/schließen" class="button button--type-text" wire:click="togglePlayerDetails()">
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
                    TODO: Focustrap wenn offen
                    <div>
                        <strong>Lebensziel:</strong> {{ $player->lebensziel->name }}
                    </div>
                    <x-gameboard.lebensziel-kompetenzen :player-id="$player->playerId" :game-events="$gameEvents" :lebensziel-phase="$player->phase" />

                    @if ($player->sumOfLoans->value > 0)
                        <div>
                            Kreditsumme: {!! $player->sumOfLoans->format() !!}
                        </div>
                    @endif
                    @if ($player->sumOfInvestments->value > 0)
                        <div>
                            Summe Investitionen: {!! $player->sumOfInvestments->format() !!}
                        </div>
                    @endif

                    <div class="text-align--center">
                        <i class="icon-phasenwechsel" aria-hidden="true"></i> {!! $player->phase->investitionen->format() !!}
                    </div>
                    <div class="text-align--center">
                        {!! $player->guthaben->format() !!}
                    </div>
                </div>
            @endif
        </div>
    @endforeach
</div>
