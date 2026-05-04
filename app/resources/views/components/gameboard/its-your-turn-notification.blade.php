<x-modal.mandatory-modal size="small">
    <x-slot:icon>
        <i class="icon-info-2" aria-hidden="true"></i>
    </x-slot:icon>

    <h3>Du bist am Zug!</h3>

    <x-slot:footer>
        <button type="button"
            @class([
                "button",
                "button--type-primary",
                $this->getPlayerColorClass()
            ])
            wire:click="startSpielzug()"
        >
            Ok
        </button>
    </x-slot:footer>
</x-modal.mandatory-modal>
