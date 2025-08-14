@props([
    '$lebenszielPhase' => null,
])
<i @class(["icon-phase-" . $lebenszielPhase]) aria-hidden="true"></i>
<span class="sr-only">Phase {{ $lebenszielPhase }}</span>
