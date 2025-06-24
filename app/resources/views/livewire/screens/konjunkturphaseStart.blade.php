@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState')
<div class="konjunkturphase-start">
    <div class="konjunkturphase-start__info">
        Die aktuelle Konjunkturphase ist: "{{KonjunkturphaseState::getCurrentKonjunkturphase($this->gameEvents())->type->value}}".
    </div>
    <button wire:click="startKonjunkturphaseForPlayer()"
            type="button"
            class="button button--type-primary">
        Weiter
    </button>

    <div class="dev-bar">
        <button type="button" class="button button--type-primary" wire:click="showLog()">Log</button>
        @if ($isLogVisible)
            <x-gameboard.log />
        @endif
    </div>
</div>
