@props([
    'size' => '', // small
])

<div x-data="{ open: true }" x-trap.noscroll="open"
    @class([
        "modal",
        "modal--type-mandatory",
        "modal--size-" . $size,
    ])
>
    <div class="modal__backdrop"></div>
    <div class="modal__content">
        @hasSection('icon_mandatory')
            <div class="modal__icon">
                @yield('icon_mandatory')
            </div>
        @endif

        @hasSection('title_mandatory')
            <div class="modal__header">
                @yield('title_mandatory')
            </div>
        @endif

        <div class="modal__body">
            @yield('content_mandatory')
        </div>

        @hasSection('footer_mandatory')
            <footer class="modal__actions">
                @yield('footer_mandatory')
            </footer>
        @endif
    </div>
</div>
