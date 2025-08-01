@props([
    '$closeModal' => '',
    '$size' => 'medium',
])

<div x-data="{ open: true }" x-trap="open"
    @class([
        "modal",
        "modal--size-$size",
    ])
>
    <div class="modal__backdrop" wire:click={{$closeModal}}></div>
    <div class="modal__content">
        <div class="modal__icon">
            @yield('icon')
        </div>
        <div class="modal__close-button">
            <button type="button" class="button button--type-borderless" wire:click={{$closeModal}}>
                <span class="sr-only">Modal schließen</span>
                <i class="icon-close" aria-hidden="true"></i>
            </button>
        </div>
        <header>
            @yield('title')
        </header>

        <div class="modal__body">
            @yield('content')
        </div>

        <footer class="modal__actions">
            @yield('footer')
        </footer>
    </div>
</div>
