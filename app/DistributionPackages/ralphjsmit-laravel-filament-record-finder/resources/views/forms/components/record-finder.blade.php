@php
    use Filament\Support\Enums\Alignment;

    $isMultiple = $isMultiple();
@endphp
<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="{
            state: $wire.$entangle('{{ $statePath = $getStatePath() }}').live,
            removeRecord(key) {
                if (@JS($isMultiple)) {
                    this.state = this.state.filter((record) => record != key)
                } else {
                    this.state = null
                }
                
                console.log(key)
            },
            reorderRecords(keys) {
                this.state = keys
            }
        }"
    >
        @php
            $alignment = $getAlignment();
            $isBadge = $isBadge();
            $isBulleted = $isBulleted();
            $isListWithLineBreaks = $isListWithLineBreaks();
            $isLimitedListExpandable = $isLimitedListExpandable();
            $isInline = $isInline();
        
            if (! $alignment instanceof Alignment) {
                $alignment = filled($alignment) ? (Alignment::tryFrom($alignment) ?? $alignment) : null;
            }

            $arrayRecordState = $getRecordState();

            if ($arrayRecordState instanceof \Illuminate\Support\Collection) {
                $arrayRecordState = $arrayRecordState->all();
            }

            $listLimit = 1;

            if (is_array($arrayRecordState)) {
                if ($listLimit = $getListLimit()) {
                    $limitedArrayRecordStateCount = (count($arrayRecordState) > $listLimit) ? (count($arrayRecordState) - $listLimit) : 0;

                    if (! $isListWithLineBreaks) {
                        $arrayRecordState = array_slice($arrayRecordState, 0, $listLimit);
                    }
                }

                $listLimit ??= count($arrayRecordState);
            }
            
            $arrayRecordState = \Illuminate\Support\Arr::wrap($arrayRecordState ?? []);

            $visiblePrefixActions = array_filter($getPrefixActions(), fn (Filament\Forms\Components\Actions\Action $action) => $action->isVisible());
            $visibleSuffixActions = array_filter($getSuffixActions(), fn (Filament\Forms\Components\Actions\Action $action) => $action->isVisible());
        @endphp
        
        <div class="flex flex-row flex-nowrap items-center gap-x-2">
            @if ($visiblePrefixActions)
                @foreach ($visiblePrefixActions as $prefixAction)
                    <div>
                        {{ $prefixAction }}
                    </div>
                @endforeach
            @endif
            <div
                @class([
                    'inline-flex flex-row-reverse items-center justify-end flex-wrap gap-x-4 gap-y-3 w-full flex-grow' => $isInline,
                    'max-w-full',
                ])
            >
                @if ($arrayRecordState)
                    <{{ $isListWithLineBreaks ? 'ul' : 'div' }}
                        @class([
                            'flex flex-row flex-wrap' => ! $isBulleted,
                            'flex-col' => (! $isBulleted) && $isListWithLineBreaks,
                            'list-disc ml-3' => $isBulleted,
                            'gap-1.5' => $isBadge,
                            'flex-wrap' => $isBadge && (! $isListWithLineBreaks),
                            'max-w-full text-gray-700 dark:text-gray-400',
                            match ($alignment) {
                                Alignment::Start => 'text-start',
                                Alignment::Center => 'text-center',
                                Alignment::End => 'text-end',
                                Alignment::Left => 'text-left',
                                Alignment::Right => 'text-right',
                                Alignment::Justify, Alignment::Between => 'text-justify',
                                default => $alignment,
                            },
                            match ($alignment) {
                                Alignment::Start, Alignment::Left => 'justify-start',
                                Alignment::Center => 'justify-center',
                                Alignment::End, Alignment::Right => 'justify-end',
                                Alignment::Between, Alignment::Justify => 'justify-between',
                                default => null,
                            } => $isBulleted || (! $isListWithLineBreaks),
                            match ($alignment) {
                                Alignment::Start, Alignment::Left => 'items-start',
                                Alignment::Center => 'items-center',
                                Alignment::End, Alignment::Right => 'items-end',
                                Alignment::Between, Alignment::Justify => 'items-stretch',
                                default => null,
                            } => $isListWithLineBreaks && (! $isBulleted),
                        ])
                        @if ($isListWithLineBreaks && $isLimitedListExpandable)
                            x-data="{ isLimited: true }"
                        @endif
                        @if ($isMultiple && ! ($isDisabled = $isDisabled()) && ($isReorderable = $isReorderable()))
                            x-sortable
                            x-on:end="reorderRecords($el.sortable.toArray())"
                        @endif
                    >
                        @foreach ($arrayRecordState as $recordState)
                            @if ($isListWithLineBreaks && (! $isLimitedListExpandable) && ($loop->iteration > $listLimit))
                                @continue
                            @endif
                            
                            @php
                                if ($recordState instanceof \Illuminate\Database\Eloquent\Model) {
                                    $recordStateKey = $recordState->getKey();
                                    $formattedState = $formatState($recordState);
                                    
                                    $recordUrl = $getRecordUrl($recordState);
                                    $badgeColor = $getBadgeColor($recordState);
                                    $badgeIcon = $getBadgeIcon($recordState);
                                    $badgeIconPosition = $getBadgeIconPosition($recordState);
                                    
                                    $shouldImplodeRecordState = ! $isListWithLineBreaks && ! $isBadge;
                                } else {
                                    $recordStateKey = $recordState;
                                    $formattedState = $recordState;
                                    
                                    $recordUrl = null;
                                    $badgeColor = null;
                                    $badgeIcon = null;
                                    $badgeIconPosition = null;
                                    $shouldImplodeRecordState = ! $isListWithLineBreaks && ! $isBadge;
                                }
                            @endphp
                            
                            <{{ $isListWithLineBreaks ? 'li' : 'div' }}
                                @if ($isListWithLineBreaks && ($loop->iteration > $listLimit))
                                    x-cloak
                                    x-show="! isLimited"
                                    x-transition
                                @endif
                                @class([
                                    'flex' => ! $isBulleted,
                                    'max-w-max' => ! ($isBulleted || $isBadge),
                                    'w-max' => $isBadge,
                                    'max-w-full',
                                ])
                                @if ($isMultiple && ! $isDisabled && $isReorderable)
                                    x-sortable-handle
                                    x-sortable-item="{{ $recordStateKey }}"
                                @endif
                            >
                                @if ($isBadge)
                                    <x-filament::badge
                                        :color="$badgeColor"
                                        :icon="$badgeIcon"
                                        :icon-position="$badgeIconPosition"
                                        :tag="$recordUrl ? 'a' : null"
                                        :href="$recordUrl"
                                        target="_blank"
                                        class="group [&_.truncate]:whitespace-normal max-w-full"
                                    >
                                        <span class="inline-flex flex-row items-center">
                                            <span
                                                @class([
                                                    'group-hover:underline' => $recordUrl,
                                                ])
                                            >
                                                {{ $formattedState }}
                                            </span>
                                            
                                            <x-filament::icon
                                                icon="heroicon-m-x-mark"
                                                class="inline shrink-0 w-4 h-4 hover:bg-gray-400/25 rounded-full hover:mix-blend-darken dark:hover:mix-blend-lighten ml-1"
                                                :x-on:click="'removeRecord(' . \Illuminate\Support\Js::from($recordStateKey) . ')'"
                                            />
                                        </span>
                                    </x-filament::badge>
                                @else
                                    <div
                                        @class([
                                            'text-base inline-flex flex-row items-center',
                                        ])
                                        class="group"
                                    >
                                        @if ($recordUrl)
                                            <x-filament::link :href="$recordUrl">
                                                {{ $formattedState }}
                                            </x-filament::link>
                                        @else
                                            <span>{{ $formattedState }}</span>
                                        @endif
                                        
                                        <x-filament::icon
                                            icon="heroicon-m-x-mark"
                                            class="inline shrink-0 w-5 h-5 cursor-pointer opacity-50 hover:bg-gray-400/25 rounded-full hover:mix-blend-darken dark:hover:mix-blend-lighten ml-1"
                                            :x-on:click="'removeRecord(' . \Illuminate\Support\Js::from($recordStateKey) . ')'"
                                        />
                                        
                                        @if ($shouldImplodeRecordState && ! $loop->last)
                                            <span class="inline whitespace-pre-wrap">, </span>
                                        @endif
                                    </div>
                                @endif
                            </{{ $isListWithLineBreaks ? 'li' : 'div' }}>
                        @endforeach
                
                        @if ($limitedArrayRecordStateCount ?? 0)
                            <{{ $isListWithLineBreaks ? 'li' : 'div' }}>
                                @if ($isListWithLineBreaks && $isLimitedListExpandable)
                                    <x-filament::link
                                        color="gray"
                                        tag="button"
                                        x-on:click.prevent="isLimited = false"
                                        x-show="isLimited"
                                    >
                                        {{ trans_choice('filament-record-finder::translations.forms.components.record-finder.expandable-limited-list.expand_list', $limitedArrayRecordStateCount) }}
                                    </x-filament::link>
                                
                                    <x-filament::link
                                        color="gray"
                                        tag="button"
                                        x-cloak
                                        x-on:click.prevent="isLimited = true"
                                        x-show="! isLimited"
                                    >
                                        {{ trans_choice('filament-record-finder::translations.forms.components.record-finder.expandable-limited-list.collapse_list', $limitedArrayRecordStateCount) }}
                                    </x-filament::link>
                                @else
                                    <span
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        {{ trans_choice('filament-record-finder::translations.forms.components.record-finder.limited-list.more_list_items', $limitedArrayRecordStateCount) }}
                                    </span>
                                @endif
                            </{{ $isListWithLineBreaks ? 'li' : 'div' }}>
                        @endif
                    </{{ $isListWithLineBreaks ? 'ul' : 'div' }}>
                @else
                    <div class="text-sm text-gray-400 dark:text-gray-500">
                        {{ $getPlaceholder() ?? __('filament-record-finder::translations.forms.components.record-finder.placeholder') }}
                    </div>
                @endif
        
                <div
                    @class([
                        'mt-3' => ! $isInline,
                    ])
                >
                    {{ $getAction('openModal') }}
                </div>
            </div>
    
            @if ($visibleSuffixActions)
                @foreach ($visibleSuffixActions as $suffixAction)
                    <div>
                        {{ $suffixAction }}
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</x-dynamic-component>
