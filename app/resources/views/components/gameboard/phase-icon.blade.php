@props([
    '$playerId' => null,
])
<i @class(["icon-phase-" . $this->getPlayerPhase($playerId ?? null)]) aria-hidden="true"></i>
<span class="sr-only">Phase {{ $this->getPlayerPhase($playerId ?? null) }}</span>
