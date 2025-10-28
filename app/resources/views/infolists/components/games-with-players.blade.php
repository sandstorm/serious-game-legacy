<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    @if ($getState() !== null)
        <table class="table-auto border-collapse border border-slate-400 w-full">
            <thead>
            <tr>
                <th class="border border-slate-300 p-1 text-left font-semibold">Spiel ID</th>
                <th class="border border-slate-300 p-1 text-left font-semibold">Spieler:innen</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($getState() as $game)
                <tr>
                    <td class="border border-slate-300 p-1">
                        <a href="{{ route('game-play.player-links', $game->id) }}" class="text-blue-600 underline" target="_blank">{{ $game->id }}</a>
                    </td>
                    <td class="border border-slate-300 p-0">
                        <table class="table-auto border-collapse w-full">
                            @foreach ($game->players as $player)
                                <tr>
                                    <td class="border border-slate-300 p-1">
                                        {{ $player->email }}
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</x-dynamic-component>
