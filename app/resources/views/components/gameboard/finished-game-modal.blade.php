@props([
    'winnerName' => '',
    'lebenszielName' => '',
])

<x-modal.mandatory-modal size="small">
    <x-slot:icon>
        <i class="icon-info" aria-hidden="true"></i>
    </x-slot:icon>

    <h3>{{ $winnerName }} hat das Lebensziel '{{ $lebenszielName }}' erreicht!</h3>
    <p>Das Spiel ist vorbei. Im Spielbericht seht ihr, wie die Partie für alle gelaufen ist.</p>

    <x-slot:footer>
        <button type="button"
                @class([
                    "button",
                    "button--type-primary",
                    $this->getPlayerColorClass()
                ])
                wire:click="endGame()"
        >
            Zum Spielbericht
        </button>
    </x-slot:footer>
</x-modal.mandatory-modal>
