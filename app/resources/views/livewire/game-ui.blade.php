@use('Domain\CoreGameLogic\Feature\Initialization\State\PreGameState')
<div>
    <h2>Game: {{ $gameId }}</h2>

    @if(PreGameState::isInPreGamePhase($this->gameStream()))
        @include("livewire.screens.pregame")
    @else

        GAME
        Ich bin Spieler: {{ PreGameState::nameForPlayer($this->gameStream(), $myself) }}<br/>

        <br/>
        <br/>

        Aktueller Spieler: {{ PreGameState::nameForPlayer($this->gameStream(), $this->currentPlayer()) }}<br/>
        <button type="button" wire:click="triggerGameAction('foo')">Game Action Foo</button><br/>
        <button type="button" wire:click="spielzugAbschliessen()">Spielzug abschließen</button>
    @endif
</div>
