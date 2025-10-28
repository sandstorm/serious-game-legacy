@props([
    'games' => [],
    'player' => null,
])

<x-layout>
    <x-slot:title>Willkommen</x-slot:title>

    <h1>Willkommen bei LeGacy</h1>
    <h3>Deine Spiele:</h3>

    @if (count($games) === 0)
        <p>Du hast aktuell keine aktiven Spiele.</p>
    @else
        <ul>
            @foreach ($games as $game)
                <li>
                    @if($game->isCreatedByPlayer())
                        <a class="button button--type-primary" href={{route('game-play.player-links', ['gameId' => $game->id])}}>
                            Dein Spiel #{{$game->id}}
                        </a>
                    @else
                        <a class="button button--type-primary" href={{route('game-play.game', ['gameId' => $game->id, 'playerId' => $player->id])}}>
                            Spiel #{{$game->id}}
                        </a>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif

    <hr />
    @if($player->can_create_games)
        <a type="button" class="button button--type-primary" href={{ @route("game-play.new-game") }}>Neues Spiel erstellen</a>
    @endif

    <hr />
    <h3>Testing</h3>

    <a type="button" class="button button--type-primary" href={{ @route("game-play.quick-start", ['players' => 2]) }}>Quick Start (2 player)</a>
    <a type="button" class="button button--type-primary" href={{ @route("game-play.quick-start", ['players' => 3]) }}>Quick Start (3 player)</a>
    <a type="button" class="button button--type-primary" href={{ @route("game-play.quick-start", ['players' => 4]) }}>Quick Start (4 player)</a>
</x-layout>
