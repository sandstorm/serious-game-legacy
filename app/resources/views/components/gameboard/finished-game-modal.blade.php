@props([
    'winnerName' => '',
    'lebenszielName' => '',
])

<x-modal.mandatory-modal size="small">
    <x-slot:icon>
        <i class="icon-info" aria-hidden="true"></i>
    </x-slot:icon>

    <h3>{{ $winnerName }} hat das Lebensziel '{{ $lebenszielName }}' erreicht!</h3>

    <x-slot:footer>
        <button type="button"
                @class([
                    "button",
                    "button--type-primary",
                    $this->getPlayerColorClass()
                ])
                wire:click="endGame()"
        >
            Spiel beenden
        </button>
    </x-slot:footer>
</x-modal.mandatory-modal>
