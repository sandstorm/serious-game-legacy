@props([
    'closeModal' => '',
    'type' => 'default', // default, borderless
    'size' => '', // small
])

<div x-data="{ open: true }" x-trap.noscroll="open" aria-modal="true" aria-labelledby="modal-headline" aria-describedby="modal-content"
    @class([
        "modal",
        "modal--type-" . $type,
        "modal--size-" . $size,
        "modal--has-footer" => view()->hasSection('footer')
    ])
>
    @if ($closeModal)
        <div class="modal__backdrop" wire:click={{$closeModal}}></div>
    @else
        <div class="modal__backdrop"></div>
    @endif
    <div class="modal__content">
        @hasSection('icon')
            <div class="modal__icon">
                @yield('icon')
            </div>
        @endif

        @if ($closeModal)
            <div class="modal__close-button">
                <button type="button" class="button button--type-borderless button--type-icon" wire:click={{$closeModal}}>
                    <span class="sr-only">Modal schließen</span>
                    <i class="icon-close" aria-hidden="true"></i>
                </button>
            </div>
        @endif

        @hasSection('title')
            <h2 class="modal__header" id="modal-headline">
                @yield('title')
            </h2>
        @endif

        <div class="modal__body" id="modal-content">
            @yield('content')
        </div>

        @hasSection('footer')
            <footer class="modal__actions">
                @yield('footer')
            </footer>
        @endif
    </div>
</div>
