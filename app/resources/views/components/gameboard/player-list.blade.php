@use('Domain\CoreGameLogic\Feature\Initialization\State\PreGameState')

@props(['myself' => null])

<ul class="player-list">
    @foreach(PreGameState::playersWithNameAndLebensziel($this->gameStream()) as $playerAndLebensziel)
        <li wire:click="showPlayerDetails('{{ $playerAndLebensziel->playerId->value }}')"
            @class([
                'player-list__player',
                'player-list__player--is-active' => $playerAndLebensziel->playerId->equals($this->getCurrentPlayer())
            ])>
            [{{$playerAndLebensziel->order }}]
            {{$playerAndLebensziel->name }}
            @if($playerAndLebensziel->playerId->equals($this->getCurrentPlayer())) (aktiver Spieler) @endif
            @if($playerAndLebensziel->playerId->equals($myself)) (Ich) @endif
        </li>
    @endforeach
</ul>
