@props([
    'games' => [],
    'player' => null,
])

<div class="games-list">
    <h3>Aktive Spiele</h3>
    @if (count($games) === 0)
        <p>Du hast aktuell keine aktiven Spiele.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Datum</th>
                    <th>Kurs</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($games as $game)
                <tr>
                    <td>
                        {{ $game->created_at->format('d.m.Y H:i') }}
                    </td>
                    <td>
                        {{ $game->course->name }}
                        @if($game->isCreatedByPlayer()) (von dir erstellt) @endif
                    </td>
                    <td>
                        @if($game->isCreatedByPlayer())
                            <a class="button button--type-primary" href={{route('game-play.player-links', ['gameId' => $game->id])}}>
                                Spiel beitreten
                            </a>
                        @else
                            <a class="button button--type-primary" href={{route('game-play.game', ['gameId' => $game->id, 'playerId' => $player->id])}}>
                                Spiel beitreten
                            </a>
                        @endif
                    </td>

                </tr>
            @endforeach
            </tbody>

        </table>
    @endif
</div>
