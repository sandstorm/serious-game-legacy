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
                'player-list__player--is-active' => $player->playersTurn
            ])>

            <ul class="zeitsteine">
                @foreach($player->zeitsteine as $playerZeitstein)
                    <li @class([
                        'zeitsteine__item',
                        'zeitsteine__item--is-used' => !$playerZeitstein->isAvailable,
                        $player->playerColor
                    ])></li>
                @endforeach
            </ul>

            {{$player->name }}
            @if($player->playersTurn) (aktiver Spieler) @endif
            @if($player->playerId->equals($myself)) (Ich) @endif
        </li>
    @endforeach
</ul>
