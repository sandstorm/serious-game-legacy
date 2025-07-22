@use('Domain\CoreGameLogic\Feature\Initialization\State\PreGameState')
@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')

@props([
    'players' => [],
    'emptySlots' => [],
])

<ul @class([
    "player-list"
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
        <li
            @class([
                'player-list__player',
                'player-list__player--is-active' => $player->isPlayersTurn,
                $player->playerColorClass,
            ])>

            <button type="button" title="Zeige Lebensziel des Spielers" class="button button--type-text" wire:click="showPlayerDetails('{{ $player->playerId->value }}')">
                {{ $player->name }}
                @if($player->playerId->equals($myself)) (Ich) @endif

                <ul class="zeitsteine">
                    @foreach($player->zeitsteine as $playerZeitstein)
                        <x-gameboard.zeitsteine.zeitstein-icon :player-name="$player->name" :player-color-class="$playerZeitstein->colorClass" :draw-empty="$playerZeitstein->drawEmpty" />
                    @endforeach
                </ul>

                <div class="player-list__player-phase">
                    {{ $player->phase }}
                </div>
            </button>
        </li>
    @endforeach
</ul>
