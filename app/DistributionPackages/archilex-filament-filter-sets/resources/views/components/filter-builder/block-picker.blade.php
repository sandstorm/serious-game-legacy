@props([
    'action',
    'afterItem' => null,
    'blocks',
    'columns' => null,
    'statePath',
    'trigger',
    'width' => null,
    'maxHeight' => null,
    'hasSearch' => false,
])

<div
    x-data="{
        search: '',
        filters: {{ collect($blocks)->map(fn ($block) => strtolower($block->getLabel()))->toJson() }}
    }"
    wire:ignore
>
    <x-filament::dropdown
        :max-height="$maxHeight"    
        :width="$width"
        shift
        teleport
        {{ $attributes->class(['fi-fo-builder-block-picker']) }}
    >
        <x-slot name="trigger">
            {{ $trigger }}
        </x-slot>

        @if ($hasSearch)
            <x-filament::dropdown.list>
                <div class="items-center gap-x-2 px-2 flex">    
                    <x-filament::icon 
                        icon="heroicon-o-magnifying-glass" 
                        class="fi-input-wrp-icon flex-shrink-0 h-5 w-5 text-gray-400 dark:text-gray-500"
                    />
                    <x-filament::input
                        autocomplete="off"
                        inline-prefix
                        :placeholder="__('filament-panels::global-search.field.placeholder')"
                        type="search"
                        x-bind:id="$id('input')"
                        x-model="search"
                    />
                </div>
            </x-filament::dropdown.list>
        @endif

        <x-filament::dropdown.list>
            <x-filament::grid
                :default="$columns['default'] ?? 1"
                :sm="$columns['sm'] ?? null"
                :md="$columns['md'] ?? null"
                :lg="$columns['lg'] ?? null"
                :xl="$columns['xl'] ?? null"
                :two-xl="$columns['2xl'] ?? null"
                direction="column"
            >
                @foreach ($blocks as $block)
                    @php
                        $wireClickActionArguments = ['block' => $block->getName()];

                        if ($afterItem) {
                            $wireClickActionArguments['afterItem'] = $afterItem;
                        }

                        $wireClickActionArguments = \Illuminate\Support\Js::from($wireClickActionArguments);

                        $wireClickAction = "mountFormComponentAction('{$statePath}', '{$action->getName()}', {$wireClickActionArguments})";
                    @endphp

                    <x-filament::dropdown.list.item
                        x-show="
                            const label = '{{ $block->getLabel() }}'
                            return label.toLowerCase().includes(search.toLowerCase())
                        "
                        :icon="$block->getIcon()"
                        x-on:click="close; setTimeout(() => {search = ''}, 500);"
                        :wire:click="$wireClickAction"
                    >
                        {{ $block->getLabel() }}
                    </x-filament::dropdown.list.item>
                @endforeach
            </x-filament::grid>

            @if ($hasSearch)
                <x-filament::dropdown.list.item
                    x-show="! filters.filter(filter => filter.includes(search.toLowerCase())).length"
                >
                    {{ __('filament-panels::global-search.no_results_message') }}
                </x-filament::dropdown.list.item>             
            @endif
        </x-filament::dropdown.list>
    </x-filament::dropdown>
</div>

