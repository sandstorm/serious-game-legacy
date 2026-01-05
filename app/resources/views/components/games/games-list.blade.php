@props([
    'games' => [],
    'player' => null,
])

<div class="games-list">
    <h3>Aktive Spiele</h3>
    @if (count($games) === 0)
        <p>Du hast aktuell keine aktiven Spiele.</p>
    @else
        @foreach ($games as $game)
            <div class="games-list__game">
                <div class="games-list__game-date">
                    <strong>Erstellt am:</strong> {{ $game->game->created_at->format('d.m.Y H:i') }}
                </div>
                <div class="games-list__game-course">
                    <strong>Kurs:</strong> {{ $game->game->course?->name }} @if($game->game->isCreatedByPlayer()) (von dir erstellt) @endif
                </div>
                <div class="games-list__game-players">
                    <strong>Mitspielende: </strong>
                    @foreach ($game->playerNames as $playerName)
                        @if ($playerName)
                            {{ $playerName }}@if(!$loop->last), @endif
                        @endif
                    @endforeach
                </div>
                <div class="games-list__game-status">
                    <strong>Status:</strong> @if ($game->isInGamePhase) Gestartet @else Vorbereitung @endif
                </div>
                <div class="games-list__game-action">
                    @if($game->game->isCreatedByPlayer())
                        <a class="button button--type-primary" href={{route('game-play.player-links', ['gameId' => $game->game->id])}}>
                            Spiel beitreten
                        </a>
                    @else
                        <a class="button button--type-primary" href={{route('game-play.game', ['gameId' => $game->game->id, 'playerId' => $player->id])}}>
                            Spiel beitreten
                        </a>
                    @endif
                </div>
            </div>
        @endforeach
    @endif
</div>
