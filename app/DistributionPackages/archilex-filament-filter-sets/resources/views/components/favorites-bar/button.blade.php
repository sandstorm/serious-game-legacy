@php
    use Archilex\AdvancedTables\Enums\FavoritesBarTheme;
    use Archilex\AdvancedTables\Support\Config;
    use Filament\Support\Enums\IconPosition;
    use Filament\Support\Enums\ActionSize;
    use Filament\Support\Enums\IconSize;
@endphp

@props([
    'badge' => null,
    'badgeColor' => null,
    'userView' => null,
    'presetViewName' => null,
    'theme' => FavoritesBarTheme::Links,
    'icon' => null,
    'tooltip' => null,
    'iconPosition' => IconPosition::Before,
    'size' => ActionSize::Medium,
    'color' => null,
])

@php
    $size = match ($size) {
        ActionSize::Small, 'sm' => ActionSize::Small,
        ActionSize::Medium, 'md' => ActionSize::Medium,
    };

    $iconPosition = match ($iconPosition) {
        IconPosition::Before, 'before' => IconPosition::Before,
        IconPosition::After, 'after' => IconPosition::After,
    };
    
    $iconSize ??= match ($size) {
        ActionSize::Small, 'sm' => IconSize::Small,
        ActionSize::Medium, 'md' => IconSize::Medium,
    };

    $lockIconSize ??= match ($size) {
        ActionSize::Small, 'sm' => IconSize::Small,
        ActionSize::Medium, 'md' => IconSize::Medium,
    };

    $lockIcon = Config::getPresetViewLockIcon();

    $iconSize = match ($iconSize) {
        IconSize::Small => 'h-4 w-4',
        IconSize::Medium => 'h-5 w-5',
    };

    $iconClasses = \Illuminate\Support\Arr::toCssClasses([
        'advanced-tables-fav-bar-item-button-icon shrink-0',
        match (true) {
            $iconPosition === IconPosition::Before && $size === ActionSize::Small => 'me-1 -ms-1',
            $iconPosition === IconPosition::Before && $size === ActionSize::Medium => 'me-1 -ms-1',
            $iconPosition === IconPosition::After && $size === ActionSize::Small => 'ms-1 -me-1',
            $iconPosition === IconPosition::After && $size === ActionSize::Medium => 'ms-1 -me-1',
        }
    ]);

    $lockIconSize = match ($lockIconSize) {
        IconSize::Small => 'h-3 w-3',
        IconSize::Medium => 'h-4 w-4',
    };

    $lockIconClasses = \Illuminate\Support\Arr::toCssClasses([
        'advanced-tables-fav-bar-item-button-lock-icon opacity-40 shrink-0',
        match (true) {
            $iconPosition === IconPosition::Before && $size === ActionSize::Small => 'ms-1 -me-1',
            $iconPosition === IconPosition::Before && $size === ActionSize::Medium => 'ms-1 -me-1',
            $iconPosition === IconPosition::After && $size === ActionSize::Small => 'me-1 -ms-1',
            $iconPosition === IconPosition::After && $size === ActionSize::Medium => 'me-1 -ms-1',
        }
    ]);

    if ($theme === FavoritesBarTheme::Links) {
        $themeClasses = \Illuminate\Support\Arr::toCssClasses([
            'border-b-2 font-medium',
            match (true) {
                $size === ActionSize::Small && $presetViewName && $lockIcon => 'pr-2',
                $size === ActionSize::Medium && $presetViewName && $lockIcon => 'pr-2.5',
                default => 'pr-1',
            },
            match (true) {
                $size === ActionSize::Small && $icon => 'min-h-[2rem] pl-2 text-sm',
                $size === ActionSize::Medium && $icon => 'min-h-[2.25rem] pl-2.5 text-sm',
                $size === ActionSize::Small && ! $icon => 'min-h-[2rem] pl-1 text-sm',
                $size === ActionSize::Medium && ! $icon => 'min-h-[2.25rem] pl-1 text-sm',
            }
        ]);

        $activeClasses = \Illuminate\Support\Arr::toCssClasses([
            match (true) {
                blank($color) => 'border-primary-500 text-primary-600 dark:border-primary-300 dark:text-primary-300',
                default => 'border-custom-500 text-custom-600 dark:border-custom-300 dark:text-custom-300',
            }
        ]);

        $activeStyles = \Illuminate\Support\Arr::toCssStyles([
            match (true) {
                blank($color) => \Filament\Support\get_color_css_variables('primary', shades: [300, 500, 600]),
                default => \Filament\Support\get_color_css_variables($color, shades: [300, 500, 600]),
            }
        ]);

        $inActiveClasses = \Illuminate\Support\Arr::toCssClasses([
            match (true) {
                blank($color) => 'border-transparent text-gray-500 hover:text-gray-600 hover:border-gray-500 focus:border-gray-500 dark:text-gray-400 dark:hover:border-gray-300 dark:hover:text-gray-300 dark:focus:border-gray-300',
                default => 'border-transparent text-custom-500 hover:text-custom-600 hover:border-custom-500 focus:border-custom-500 dark:text-custom-400 dark:hover:border-custom-300 dark:hover:text-custom-300 dark:focus:border-custom-300',
            }
        ]);

        $inActiveStyles = \Illuminate\Support\Arr::toCssStyles([
            match (true) {
                blank($color) => \Filament\Support\get_color_css_variables('gray', shades: [300, 400, 500, 600]),
                default => \Filament\Support\get_color_css_variables($color, shades: [300, 400, 500, 600]),
            }
        ]);
    } elseif ($theme === FavoritesBarTheme::SimpleLinks) {
        $themeClasses = \Illuminate\Support\Arr::toCssClasses([
            'font-medium',
            match (true) {
                $size === ActionSize::Small && $presetViewName && $lockIcon => 'pr-1.5',
                $size === ActionSize::Medium && $presetViewName && $lockIcon => 'pr-2',
                default => 'pr-1',
            },
            match (true) {
                $size === ActionSize::Small && $icon => 'min-h-[2rem] pl-2 text-sm',
                $size === ActionSize::Medium && $icon => 'min-h-[2.25rem] pl-2.5 text-sm',
                $size === ActionSize::Small && ! $icon => 'min-h-[2rem] text-sm',
                $size === ActionSize::Medium && ! $icon => 'min-h-[2.25rem] text-sm',
            }
        ]);

        $activeClasses = \Illuminate\Support\Arr::toCssClasses([
            match (true) {
                blank($color) => 'text-primary-600 dark:text-primary-300',
                default => 'text-custom-600 dark:text-custom-300',
            }
        ]);

        $activeStyles = \Illuminate\Support\Arr::toCssStyles([
            match (true) {
                blank($color) => \Filament\Support\get_color_css_variables('primary', shades: [300, 600]),
                default => \Filament\Support\get_color_css_variables($color, shades: [300, 600]),
            }
        ]);

        $inActiveClasses = \Illuminate\Support\Arr::toCssClasses([
            match (true) {
                blank($color)=> 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300',
                default => 'text-custom-500 hover:text-custom-600 focus:border-custom-500 dark:text-custom-400 dark:hover:text-custom-300',
            }
        ]);

        $inActiveStyles = \Illuminate\Support\Arr::toCssStyles([
            match (true) {
                blank($color)=> \Filament\Support\get_color_css_variables('gray', shades: [300, 400, 500, 700]),
                default => \Filament\Support\get_color_css_variables($color, shades: [300, 400, 500, 600, 700]),
            }
        ]);
    } elseif ($theme === FavoritesBarTheme::Tabs) {
        $themeClasses = \Illuminate\Support\Arr::toCssClasses([
            'font-medium rounded-lg',
            match ($size) {
                ActionSize::Small => 'min-h-[2rem] px-3 text-sm',
                ActionSize::Medium => 'min-h-[2.25rem] px-4 text-sm',
            }
        ]);

        $activeClasses = \Illuminate\Support\Arr::toCssClasses([
            match (true) {
                blank($color) => 'bg-gray-200/50 text-gray-800 dark:bg-gray-700/50 dark:text-gray-100',
                default => 'bg-custom-500 text-white',
            }
        ]);

        $activeStyles = \Illuminate\Support\Arr::toCssStyles([
            match (true) {
                blank($color) => \Filament\Support\get_color_css_variables($color, shades: [100, 200, 700, 800]),
                default => \Filament\Support\get_color_css_variables($color, shades: [500]),
            }
        ]);

        $inActiveClasses = \Illuminate\Support\Arr::toCssClasses([
            match (true) {
                blank($color) => 'text-gray-500 hover:text-gray-800 hover:bg-gray-200/50 focus:bg-gray-200/50 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:bg-gray-700/50 dark:focus:bg-gray-700/50',
                default => 'text-custom-500 hover:text-white hover:bg-custom-500 focus:bg-custom-500 focus:text-white',
            }
        ]);

        $inActiveStyles = \Illuminate\Support\Arr::toCssStyles([
            match (true) {
                blank($color) => \Filament\Support\get_color_css_variables('gray', shades: [200, 300, 400, 500, 700, 800]),
                default => \Filament\Support\get_color_css_variables($color, shades: [500]),
            }
        ]);
    } elseif ($theme === FavoritesBarTheme::BrandedTabs) {
        $themeClasses = \Illuminate\Support\Arr::toCssClasses([
            'font-medium rounded-lg',
            match ($size) {
                ActionSize::Small => 'min-h-[2rem] px-3 text-sm',
                ActionSize::Medium => 'min-h-[2.25rem] px-4 text-sm',
            }
        ]);

        $activeClasses = \Illuminate\Support\Arr::toCssClasses([
            match (true) {
                blank($color) => 'bg-primary-500 text-white dark:bg-white/5',
                default => 'bg-custom-500 text-white',
            }
        ]);

        $activeStyles = \Illuminate\Support\Arr::toCssStyles([
            match (true) {
                blank($color) => \Filament\Support\get_color_css_variables('primary', shades: [500]),
                default => \Filament\Support\get_color_css_variables($color, shades: [500]),
            }
        ]);

        $inActiveClasses = \Illuminate\Support\Arr::toCssClasses([
            match (true) {
                blank($color) => 'text-gray-500 hover:text-gray-800 hover:bg-gray-200/50 focus:bg-gray-200/50 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:bg-white/5 dark:focus:bg-white/5',
                default => 'text-custom-500 hover:text-white hover:bg-custom-500 focus:bg-custom-500 focus:text-white',
            }
        ]);

        $inActiveStyles = \Illuminate\Support\Arr::toCssStyles([
            match (true) {
                blank($color) => \Filament\Support\get_color_css_variables('gray', shades: [200, 300, 400, 500, 700, 800]),
                default => \Filament\Support\get_color_css_variables($color, shades: [500]),
            }
        ]);
    } elseif ($theme === FavoritesBarTheme::Github) {
        $themeClasses = \Illuminate\Support\Arr::toCssClasses([
            'relative mb-2 rounded-lg',
            match ($size) {
                ActionSize::Small => 'min-h-[2.25rem] px-2.5 text-sm',
                ActionSize::Medium => 'min-h-[2.25rem] px-3 text-sm',
            }
        ]);

        $activeClasses = \Illuminate\Support\Arr::toCssClasses([
            'text-gray-800 font-semibold hover:bg-gray-200/50 dark:text-gray-100 dark:hover:bg-gray-800/50 after:rounded-full after:-bottom-2 after:absolute after:w-full after:content[""] after:h-0.5 after:right-0',
            match (true) {
                blank($color) => 'after:bg-primary-500',
                default => 'after:bg-custom-500',
            }
        ]);

        $activeStyles = \Illuminate\Support\Arr::toCssStyles([
            \Filament\Support\get_color_css_variables('gray', shades: [100, 200, 800]),
            match (true) {
                blank($color) => \Filament\Support\get_color_css_variables('primary', shades: [500]),
                default => \Filament\Support\get_color_css_variables($color, shades: [500]),
            }
        ]);

        $inActiveClasses = 'font-medium text-gray-700 hover:bg-gray-200/50 dark:text-gray-100 dark:hover:bg-gray-900';

        $inActiveStyles = \Filament\Support\get_color_css_variables('gray', shades: [100, 200, 700, 900]);

        $iconClasses = \Illuminate\Support\Arr::toCssClasses([
            'text-gray-400 dark:text-gray-500',
            $iconClasses,
        ]);
    } elseif ($theme === FavoritesBarTheme::Filament) {
        $themeClasses = \Illuminate\Support\Arr::toCssClasses([
            'font-medium gap-x-2 rounded-lg hover:bg-gray-50 focus:bg-gray-50 dark:hover:bg-white/5 dark:focus:bg-white/5',
            match ($size) {
                ActionSize::Small => 'min-h-[2rem] px-3 text-sm',
                ActionSize::Medium => 'min-h-[2.25rem] px-4 text-sm',
            }
        ]);

        $activeClasses = \Illuminate\Support\Arr::toCssClasses([
            match (true) {
                blank($color) => 'bg-gray-50 text-primary-600 dark:bg-white/5 dark:text-primary-400',
                default => 'bg-custom-50 text-custom-600 dark:bg-white/5 dark:text-custom-400',
            }
        ]);

        $activeStyles = \Illuminate\Support\Arr::toCssStyles([
            match (true) {
                blank($color) => \Filament\Support\get_color_css_variables('primary', shades: [50, 400, 600]),
                default => \Filament\Support\get_color_css_variables($color, shades: [50, 400, 600]),
            }
        ]);

        $inActiveClasses = \Illuminate\Support\Arr::toCssClasses([
            match (true) {
                blank($color) => 'text-gray-500 hover:text-gray-700 focus:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 dark:focus:text-gray-200',
                default => 'text-custom-500 hover:text-custom-700 focus:text-custom-700 dark:text-custom-400 dark:hover:text-custom-200 dark:focus:text-custom-200',
            }
        ]);

        $inActiveStyles = \Illuminate\Support\Arr::toCssStyles([
            match (true) {
                blank($color) => \Filament\Support\get_color_css_variables('gray', shades: [200, 400, 500, 700]),
                default => \Filament\Support\get_color_css_variables($color, shades: [200, 400, 500, 700]),
            }
        ]);

        $iconClasses = "advanced-tables-fav-bar-item-button-icon shrink-0";
    }

    $buttonClasses = \Illuminate\Support\Arr::toCssClasses([
        'advanced-tables-fav-bar-item-button flex items-center justify-center gap-1 py-1 transition duration-75 outline-none',
        $themeClasses,
    ]);
@endphp

<li 
    @if (filled($tooltip))
        x-data="{}"
        x-tooltip="{
            content: @js($tooltip),
            theme: $store.theme,
        }"
    @endif
    wire:key="{{ $userView ? $userView->id : ($presetViewName ?: 'default') }}"
    {{ $attributes->merge(['class' => 'advanced-tables-fav-bar-item']) }}
>
    <button
        type="button"
        @if (filled($presetViewName))
            x-on:click="
                $wire.call('loadPresetView', '{{ $presetViewName }}')
            "
        @elseif ($userView)
            x-on:click="
                $wire.call('loadUserView', {{ $userView->id }}, {{ json_encode($userView->filters) }} )
            "
        @else
            x-on:click="
                $wire.call('resetTableToDefault')
            "
        @endif

        @if (filled($presetViewName))
            :class="
                (! activeUserView) && 
                activePresetView == '{{ $presetViewName }}'
                    ? '{{ $activeClasses }}'
                    : '{{ $inActiveClasses }}'
            "

            :style="
                (! activeUserView) && 
                activePresetView == '{{ $presetViewName }}' 
                    ? '{{ $activeStyles }}'
                    : '{{ $inActiveStyles }}'
            "
        @elseif ($userView)
            :class="
                activeUserView == {{ $userView->id }}
                    ? '{{ $activeClasses }}'
                    : '{{ $inActiveClasses }}'
            "

            :style="
                activeUserView == {{ $userView->id }}
                    ? '{{ $activeStyles }}'
                    : '{{ $inActiveStyles }}'
            "
        @else
            :class="
                defaultViewIsActive
                    ? '{{ $activeClasses }}'
                    : '{{ $inActiveClasses }}'
            " 
        @endif

        class="{{ $buttonClasses }}"
    >
        @if ($icon && $iconPosition === IconPosition::Before)
            <x-filament::icon
                :icon="$icon"
                :class="$iconClasses . ' ' . $iconSize"
            /> 
        @endif

        @if ($presetViewName && $lockIcon && $iconPosition === IconPosition::After) 
            <x-filament::icon
                :icon="$lockIcon"
                :class="$lockIconClasses . ' ' . $lockIconSize"
            />    
        @endif

        <span
            {!! $theme === FavoritesBarTheme::Github ? 'data-content="' . $slot .'"' : '' !!}
            @class([
                'whitespace-nowrap',
                'before:block before:content-[attr(data-content)] before:font-bold before:h-0 before:invisible' => $theme === FavoritesBarTheme::Github,
            ])
        >
            {{ $slot }}
        </span>

        @if ($icon && $iconPosition === IconPosition::After)
            <x-filament::icon
                :icon="$icon"
                :class="$iconClasses . ' ' . $iconSize"
            /> 
        @endif

        @if ($presetViewName && $lockIcon && $iconPosition === IconPosition::Before) 
            <x-filament::icon
                :icon="$lockIcon"
                :class="$lockIconClasses . ' ' . $lockIconSize"
            />   
        @endif

        @if (filled($badge) && $theme === FavoritesBarTheme::Github)
            <div class="bg-gray-300/50 text-xs font-medium px-2 py-0.5 rounded-xl flex items-center justify-center ms-1 -me-1 dark:bg-gray-800">
                {{ $badge }}
            </div>
        @elseif (filled($badge))
            <x-filament::badge 
                class="ms-1"
                size="sm"
                color="{{ $theme === FavoritesBarTheme::Tabs ? ($badgeColor ?? 'gray') : ($badgeColor ?? 'primary') }}"
            >
                {{ $badge }}
            </x-filament::badge>
        @endif
    </button>
</li>
