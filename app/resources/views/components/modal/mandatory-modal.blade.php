@props([
    'size' => '', // small
])

<div x-data="{ open: true }" x-trap.inert.noscroll="open" role="alertdialog" aria-modal="true" aria-modal="true" aria-labelledby="mandatory-modal-headline" aria-describedby="mandatory-modal-content"
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
            <h2 class="modal__header" id="mandatory-modal-headline">
                @yield('title_mandatory')
            </h2>
        @endif

        <div class="modal__body" id="mandatory-modal-content">
            @yield('content_mandatory')
        </div>

        @hasSection('footer_mandatory')
            <footer class="modal__actions">
                @yield('footer_mandatory')
            </footer>
        @endif
    </div>
</div>
