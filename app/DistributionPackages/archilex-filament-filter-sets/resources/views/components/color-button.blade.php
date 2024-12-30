@php
    use Filament\Support\Enums\ActionSize;
    use Filament\Support\Enums\IconSize;
@endphp

@props([
    'color' => 'primary',
    'disabled' => false,
    'icon' => null,
    'iconSize' => null,
    'label' => null,
    'size' => 'md',
    'tag' => 'button',
    'tooltip' => null,
    'type' => 'button',
])

@php    
    $iconSize ??= match ($size) {
        ActionSize::ExtraSmall, 'xs' => IconSize::Small,
        ActionSize::Small, ActionSize::Medium, 'sm', 'md' => IconSize::Medium,
        ActionSize::Large, ActionSize::ExtraLarge, 'lg', 'xl' => IconSize::Large,
    };

    $buttonClasses = \Illuminate\Support\Arr::toCssClasses([
        'advanced-tables-color-button text-white flex flex-shrink-0 items-center justify-center rounded-full relative outline-none disabled:opacity-70 disabled:cursor-not-allowed disabled:pointer-events-none transition focus-visible:ring-2',
        match ($size) {
            ActionSize::ExtraSmall, 'xs' => 'h-6 w-6',
            ActionSize::Small, 'sm' => 'h-8 w-8',
            ActionSize::Medium, 'md' => 'h-10 w-10',
            ActionSize::Large, 'lg' => 'h-12 w-12',
            ActionSize::ExtraLarge, 'xl' => 'h-14 w-14',
            default => $size,
        },
        match ($color) {
            'gray' => 'bg-gray-300 hover:bg-gray-200 focus-visible:ring-gray-200/50 dark:bg-gray-600 dark:hover:bg-gray-500 dark:text-gray-100 dark:focus-visible:ring-gray-500/50',
            default => 'bg-custom-600 hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:focus:ring-custom-400/50',
        }, 
    ]);

    $buttonStyles = \Illuminate\Support\Arr::toCssStyles([
        \Filament\Support\get_color_css_variables($color, shades: [400, 500, 600]) => $color !== 'gray',
    ]);

    $iconClasses = \Illuminate\Support\Arr::toCssClasses([
        'advanced-tables-color-button-icon',
        match ($iconSize) {
            IconSize::Small, 'sm' => 'h-4 w-4',
            IconSize::Medium, 'md' => 'h-5 w-5',
            IconSize::Large, 'lg' => 'h-6 w-6',
            default => $iconSize,
        },
    ]);

    $wireTarget = $attributes->whereStartsWith(['wire:target', 'wire:click'])->filter(fn ($value): bool => filled($value))->first();
@endphp

@if ($tag === 'button')
    <button
        @if ($tooltip)
            x-data="{}"
            x-tooltip="{
                content: @js($tooltip),
                theme: $store.theme,
            }"
        @endif
        {{
            $attributes
                ->merge([
                    'disabled' => $disabled,
                    'title' => $label,
                    'type' => $type,
                ], escape: false)
                ->class([$buttonClasses])
                ->style([$buttonStyles])
        }}
    >
        @if ($label)
            <span class="sr-only">
                {{ $label }}
            </span>
        @endif

        <x-filament::icon
            x-show="state === '{{ $color }}' || '{{ $color }}' === 'none'"
            :icon="$icon"
            :class="$iconClasses"
        />
    </button>
@endif
