<div x-data="{ open: true }" x-trap.noscroll="open"
    @class([
        "modal",
        "modal--type-mandatory",
    ])
>
    <div class="modal__backdrop"></div>
    <div class="modal__content">
        @hasSection('icon')
            <div class="modal__icon">
                @yield('icon')
            </div>
        @endif

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
