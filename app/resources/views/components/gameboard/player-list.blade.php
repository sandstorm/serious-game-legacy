@use('Domain\CoreGameLogic\Feature\Initialization\State\PreGameState')
@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')

@props([
    'players' => []
])

<ul class="player-list">
    @foreach($players as $player)
        <li wire:click="showPlayerDetails('{{ $player->playerId->value }}')"
            @class([
                'player-list__player',
                'player-list__player--is-active' => $player->isPlayersTurn
            ])>

            <ul class="zeitsteine">
                @foreach($player->zeitsteine as $playerZeitstein)
                    <x-gameboard.zeitsteine.zeitstein :player-color-class="$playerZeitstein->colorClass" :draw-empty="$playerZeitstein->drawEmpty" />
                @endforeach
            </ul>

            {{$player->name }}
            @if($player->isPlayersTurn) (aktiver Spieler) @endif
            @if($player->playerId->equals($myself)) (Ich) @endif
        </li>
    @endforeach
</ul>
