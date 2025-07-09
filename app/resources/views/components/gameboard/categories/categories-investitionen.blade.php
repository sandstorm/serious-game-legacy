@props([
    'category' => null,
    'gameEvents' => null,
    '$playerId' => null,
])

<button type="button" class="button button--type-primary" wire:click="toggleInvestitionen()">
    Investieren
</button>

@if ($this->showInvestitionen)
    <x-gameboard.investitionen.invenstitionen-modal :game-events="$gameEvents" :player-id="$playerId" />
@endif
