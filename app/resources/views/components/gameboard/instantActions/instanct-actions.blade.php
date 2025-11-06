<div class="instant-actions">
    <div class="instant-action">
        <button
            type="button"
            @class([
                "button",
                "button--type-secondary",
                "button--disabled" => !$this->canStartWeiterbildung(),
            ])
            wire:click="showWeiterbildung()"
        >
            <span>Weiterbildung <small>sofort</small></span>
            <div class="button__suffix">
                <div>
                    <i class="icon-plus text--success" aria-hidden="true"></i>
                    <x-gameboard.kompetenzen.kompetenz-icon-bildung :draw-half-empty="true" />
                    <span class="sr-only">Du bekommst eine halbe Bildungskompetenz</span>
                </div>
                <div>
                    <i class="icon-minus text--danger" aria-hidden="true"></i><i class="icon-zeitstein" aria-hidden="true"></i>
                    <span class="sr-only">Kostet einen Zeitstein</span>
                </div>
            </div>
        </button>
    </div>
    <div class="instant-action">
        <button
            type="button"
            @class([
                "button",
                "button--type-secondary",
                "button--disabled" => !$this->canDoMinijob(),
            ])
            wire:click="doMinijob()"
        >
            <span>Minijob <small>sofort</small></span>
            <div class="button__suffix">
                <div>
                    <i class="icon-plus text--success" aria-hidden="true"></i><i class="icon-euro" aria-hidden="true"></i>
                    <span class="sr-only">Du bekommst eine einmalige Zahlung</span>
                </div>
                <div>
                    <i class="icon-minus text--danger" aria-hidden="true"></i><i class="icon-zeitstein" aria-hidden="true"></i>
                    <span class="sr-only">Kostet einen Zeitstein</span>
                </div>
            </div>
        </button>
    </div>
</div>
