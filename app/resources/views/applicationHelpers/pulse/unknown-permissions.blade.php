<x-pulse::card :cols="$cols" :rows="$rows" :class="$class" wire:poll.5s="">
    <x-pulse::card-header name="Unknown Permissions">
        <x-slot:icon>
            @svg('heroicon-o-shield-exclamation')
        </x-slot:icon>
        <x-slot:details>
            Permissions which are not handled by <code>AppAuthorizer</code>
        </x-slot:details>
    </x-pulse::card-header>

    <x-pulse::scroll :expand="$expand">
        <div class="min-h-full flex flex-col">
            @if ($unknownPermissions->isEmpty())
                <x-pulse::no-results />
            @else
                <x-pulse::table>
                    <colgroup>
                        <col width="100%" />
                        <col width="0%" />
                        <col width="0%" />
                    </colgroup>
                    <x-pulse::thead>
                        <tr>
                            <x-pulse::th>Ability</x-pulse::th>
                            <x-pulse::th>Object</x-pulse::th>
                            <x-pulse::th class="text-right">Latest</x-pulse::th>
                            <x-pulse::th class="text-right">Count</x-pulse::th>
                            <x-pulse::th></x-pulse::th>
                        </tr>
                    </x-pulse::thead>
                    <tbody>
                    @foreach ($unknownPermissions->take(100) as $permission)
                        <tr wire:key="{{ $permission->id }}-row">
                            <x-pulse::td>
                                <code class="block text-xs text-gray-900 dark:text-gray-100 truncate" title="{{ $permission->ability }}">
                                    {{ $permission->ability }}
                                </code>
                            </x-pulse::td>
                            <x-pulse::td>
                                <code class="block text-xs text-gray-900 dark:text-gray-100 truncate" title="{{ $permission->object }}">
                                    {{ $permission->object }}
                                </code>
                            </x-pulse::td>

                            <x-pulse::td numeric class="text-gray-700 dark:text-gray-300 font-bold">
                                {{ $permission->last_seen->ago(syntax: Carbon\CarbonInterface::DIFF_ABSOLUTE, short: true) }}
                            </x-pulse::td>
                            <x-pulse::td numeric class="text-gray-700 dark:text-gray-300 font-bold">
                                {{ $permission->count }}
                            </x-pulse::td>
                            <x-pulse::td>
                                <button wire:click="removeUnknownPermission({{ $permission->id }})">
                                    <div class="[&>svg]:flex-shrink-0 [&>svg]:w-6 [&>svg]:h-6 [&>svg]:stroke-gray-400 [&>svg]:dark:stroke-gray-600">
                                        @svg('heroicon-o-trash')
                                    </div>
                                </button>
                            </x-pulse::td>
                        </tr>
                    @endforeach
                    </tbody>
                </x-pulse::table>
            @endif

            @if ($unknownPermissions->count() > 100)
                <div class="mt-2 text-xs text-gray-400 text-center">Limited to 100 entries</div>
            @endif
        </div>
    </x-pulse::scroll>
</x-pulse::card>