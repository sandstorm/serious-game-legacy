@use(tbQuar\Facades\Quar);

@props([
    'gameId' => null,
    'playerIds' => [],
    'player' => null,
])

<x-layout>
    <a class="button button--type-primary" href={{route('game-play.index')}}>Zurück zur Übersicht</a>
    <hr />
    <h2>Spiel {{$gameId->value}}</h2>

    <a class="button button--type-primary" href={{route('game-play.game', ['gameId' => $gameId, 'playerId' => $player->id])}}>Spiel starten</a>

    <hr />
    <h3>Links für deine Mitspieler:innen:</h3>
    <ul>
        @foreach($playerIds as $k => $playerId)
            @if ($playerId->value === $player->id)
                @continue
            @endif
            <li>
                Spieler {{ $k+1 }}: <a href={{route('game-play.game', ['gameId' => $gameId, 'playerId' => $playerId])}}>{{$playerId->value}}</a>
                {{ Quar::generate(route('game-play.game', ['gameId' => $gameId, 'playerId' => $playerId])) }}
            </li>
        @endforeach
    </ul>
</x-layout>
