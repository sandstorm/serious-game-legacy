@use('Domain\CoreGameLogic\Feature\Initialization\State\PreGameState')
@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')

@props(['myself' => null])

<ul class="player-list">
    @foreach(PreGameState::playersWithNameAndLebensziel($this->gameEvents()) as $playerAndLebensziel)
        <li wire:click="showPlayerDetails('{{ $playerAndLebensziel->playerId->value }}')"
            @class([
                'player-list__player',
                'player-list__player--is-active' => $playerAndLebensziel->playerId->equals($this->getCurrentPlayer()),
                'player-list__player--is-active' => $playerAndLebensziel->playerId->equals($this->getCurrentPlayer())
            ])>

            <ul class="zeitsteine">
                @for($i = 0; $i < PlayerState::getZeitsteineForPlayer($this->gameEvents(), $playerAndLebensziel->playerId); $i++)
                    <li class="zeitsteine__item" @style(['background-color:' . PlayerState::getPlayerColor($this->gameEvents(), $playerAndLebensziel->playerId)])></li>
                @endfor
            </ul>
            {{$playerAndLebensziel->name }}
            @if($playerAndLebensziel->playerId->equals($this->getCurrentPlayer())) (aktiver Spieler) @endif
            @if($playerAndLebensziel->playerId->equals($myself)) (Ich) @endif
        </li>
    @endforeach
</ul>
