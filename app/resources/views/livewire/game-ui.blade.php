<div>
    Game: {{ $gameId }}<br />
    Ich bin Spieler: {{ $myself }}<br />
    <br />
    <br />

    Aktueller Spieler: {{ $this->currentPlayer()->value }}<br />
    <button type="button" wire:click="triggerGameAction('foo')">Game Action Foo</button><br />
    <button type="button" wire:click="spielzugAbschliessen()">Spielzug abschließen</button>
</div>
