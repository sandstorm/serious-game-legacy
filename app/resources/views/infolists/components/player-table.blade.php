<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <div>
        <table class="table-auto">
            <thead>
                <tr>
                    <th>SoSciSurvey ID</th>
                    <th>Password</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($getState() as $player)
                    <tr>
                        <td>{{ $player->soscisurvey_id }}</td>
                        <td>{{ $player->password }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-dynamic-component>
