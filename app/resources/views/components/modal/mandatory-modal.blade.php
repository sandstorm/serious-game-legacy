@props([
    'size' => '', // small
])

<div
    x-data="{ open: true }"
    x-trap.inert.noscroll="open"
    role="alertdialog"
    aria-modal="true"
    aria-labelledby="mandatory-modal-headline"
    aria-describedby="mandatory-modal-content"
    @class([
        "modal",
        "modal--type-mandatory",
        "modal--size-" . $size,
    ])
>
    <div class="modal__backdrop"></div>
    <div class="modal__content">
        @isset($icon)
            <div class="modal__icon">
                {{ $icon }}
            </div>
        @endisset

        @isset($title)
            <h2 class="modal__header" id="mandatory-modal-headline">
                {{ $title }}
            </h2>
        @endisset

        <div class="modal__body" id="mandatory-modal-content">
            {{ $slot }}
        </div>

        @isset($footer)
            <footer class="modal__actions">
                {{ $footer }}
            </footer>
        @endisset
    </div>
</div>
