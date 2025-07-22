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
        <li wire:click="showPlayerDetails('{{ $player->playerId->value }}')"
            @class([
                'player-list__player',
                'player-list__player--is-active' => $player->isPlayersTurn,
                $player->playerColorClass,
            ])>

            {{ $player->name }}
            @if($player->playerId->equals($myself)) (Ich) @endif

            <ul class="zeitsteine">
                @foreach($player->zeitsteine as $playerZeitstein)
                    <x-gameboard.zeitsteine.zeitstein :player-name="$player->name" :player-color-class="$playerZeitstein->colorClass" :draw-empty="$playerZeitstein->drawEmpty" />
                @endforeach
            </ul>

            <div class="player-list__player-phase">
                {{ $player->phase }}
            </div>
        </li>
    @endforeach
</ul>
