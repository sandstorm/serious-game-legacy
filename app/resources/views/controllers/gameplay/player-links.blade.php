@props([
    'gameId' => null,
    'playerIds' => [],
    'player' => null,
])

<x-layout>
    <x-slot:title>Spiel beitreten</x-slot:title>
    <header class="game-header">
        <a class="button button--type-text" href={{route('game-play.index')}}>Zurück zur Übersicht</a>
    </header>

    <div class="game-links" x-data="shareLink">
        <h1>Teile die Links mit deinen Mitspielenden</h1>
        <table>
            <tbody>
            <tr>
                <td>
                    Spieler 1 (Du):
                </td>
                <td>
                    <a class="button button--type-primary" href={{route('game-play.game', ['gameId' => $gameId, 'playerId' => $player->id])}}>
                        Spiel beitreten
                    </a>
                </td>
            </tr>
            @foreach($playerIds as $k => $playerId)
                @if ($playerId->value === $player->id)
                    @continue
                @endif
                <tr>
                    <td>
                        Spieler {{ $k+1 }}:
                    </td>
                    <td>
                        <button
                            type="button"
                            class="button button--type-secondary"
                            x-on:click="shareLink('Spiel beitreten','{{ route('game-play.game', ['gameId' => $gameId, 'playerId' => $playerId->value]) }}')"
                        >
                            Link teilen <i class="icon-share"></i>
                        </button>
                    </td>
                </tr>
            @endforeach
            </tbody>

        </table>
    </div>

</x-layout>
