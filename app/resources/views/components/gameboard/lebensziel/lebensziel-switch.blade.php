@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')

@props([
    'lebenszielPhase' => null,
    'currentPhase' => null,
])

<div class="lebensziel__phase-switch">
    @if ($currentPhase !== $lebenszielPhase)
        <div
            @class([
                "button",
                "button--type-primary",
                "button--disabled",
                $this->getPlayerColorClass(),
            ])
        >
            <i class="icon-lock-closed"></i>
            @if ($lebenszielPhase === 3)
                Lebensziel <br /> abschließen
            @else
                Phase <br /> wechseln
            @endif
        </div>
    @else
        <button
            type="button"
            @class([
                "button",
                "button--type-primary",
                "button--disabled" => !$this->canChangeLebenszielphase(),
                $this->getPlayerColorClass(),
            ])
            wire:click="changeLebenszielphase()"
        >
            @if($this->canChangeLebenszielphase())
                <i class="icon-lock-open"></i>
            @else
                <i class="icon-lock-closed"></i>
            @endif

            @if ($lebenszielPhase === 3)
                Lebensziel <br /> abschließen
            @else
                Phase <br /> wechseln
            @endif
        </button>
    @endif

</div>
