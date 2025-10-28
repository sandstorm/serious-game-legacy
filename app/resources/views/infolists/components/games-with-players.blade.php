
<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    @if ($getState() !== null)
        <table class="table-auto border-collapse border border-slate-400 w-full">
            <thead>
            <tr>
                <th class="border border-slate-300 p-1 text-left font-semibold">Spiel</th>
                <th class="border border-slate-300 p-1 text-left font-semibold">Von Spieler erstellt</th>
                <th class="border border-slate-300 p-1 text-left font-semibold">Spieler:innen</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach ($getState() as $game)
                <tr>
                    <td class="border border-slate-300 p-1">
                        Spiel {{ $loop->index + 1 }}
                    </td>
                    <td class="border border-slate-300 p-1">
                        {{ $game->isCreatedByPlayer() ? 'Ja' : 'Nein' }}
                    </td>
                    <td class="border border-slate-300 p-1">
                        <ul>
                        @foreach ($game->players as $player)
                            <li>
                                {{ $player->email }}
                                @if ($player->name === $game->getCreatorName())
                                    (Ersteller:in)
                                @endif
                            </li>
                        @endforeach
                        </ul>
                    </td>
                    <td class="border border-slate-300 p-1">
                        <a href="{{ \App\Filament\Admin\Resources\GameResource::getUrl('edit', ['record' => $game->id]) }}">
                            bearbeiten
                        </a>

                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</x-dynamic-component>
