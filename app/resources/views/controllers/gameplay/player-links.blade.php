@use(Domain\CoreGameLogic\Dto\ValueObject\GameId)
<x-layout>
    <h2>Spiel {{$gameId->value}}</h2>

    <ul>
        @foreach($playerIds as $k => $playerId)
            <li><a href={{route('game-play.game', ['gameId' => $gameId, 'myselfId' => $playerId])}}>Spieler {{$k+1}} {{$playerId->value}}</a></li>
        @endforeach
    </ul>
</x-layout>
