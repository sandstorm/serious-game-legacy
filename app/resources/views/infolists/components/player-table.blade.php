<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    @if ($getState() !== null)
        <table class="table-auto border-collapse border border-slate-400 w-full">
            <thead>
                <tr>
                    <th class="border border-slate-300 p-1 text-left font-semibold">SoSciSurvey ID</th>
                    <th class="border border-slate-300 p-1 text-left font-semibold">Password</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($getState() as $player)
                    <tr>
                        <td class="border border-slate-300 p-1">{{ $player->soscisurvey_id }}</td>
                        <td class="border border-slate-300 p-1">{{ $player->password }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</x-dynamic-component>
