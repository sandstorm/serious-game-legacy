@props([
    'closeModal' => '',
    'type' => 'default', // default, borderless
])

<div x-data="{ open: true }" x-trap.noscroll="open"
    @class([
        "modal",
        "modal--type-" . $type,
    ])
>
    <div class="modal__backdrop" wire:click={{$closeModal}}></div>
    <div class="modal__content">
        @hasSection('icon')
            <div class="modal__icon">
                @yield('icon')
            </div>
        @endif

        <div class="modal__close-button">
            <button type="button" class="button button--type-borderless" wire:click={{$closeModal}}>
                <span class="sr-only">Modal schließen</span>
                <i class="icon-close" aria-hidden="true"></i>
            </button>
        </div>

        @hasSection('title')
            <div class="modal__header">
                @yield('title')
            </div>
        @endif

        <div class="modal__body">
            @yield('content')
        </div>

        @hasSection('footer')
            <footer class="modal__actions">
                @yield('footer')
            </footer>
        @endif
    </div>
</div>
