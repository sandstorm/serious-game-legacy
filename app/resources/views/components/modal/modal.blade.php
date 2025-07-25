@props([
    '$closeModal' => '',
    '$size' => 'medium',
])

<div @class([
    "modal",
    "modal--size-$size",
])>
    <div class="modal__backdrop" wire:click={{$closeModal}}></div>
    <div class="modal__content">
        <div class="modal__icon">
            @yield('icon')
        </div>
        <div class="modal__close-button">
            <button type="button" class="button button--type-text" wire:click={{$closeModal}}>
                <span class="sr-only">Modal schließen</span>
                <i class="icon-close" aria-hidden="true"></i>
            </button>
        </div>
        <header>
            <span>@yield('title')</span>
        </header>

        <div class="modal__body">
            @yield('content')
        </div>

        <footer class="modal__actions">
            @yield('footer')
        </footer>
    </div>
</div>
