@use('Domain\CoreGameLogic\Feature\Initialization\State\PreGameState')

{{-- !!! Livewire components MUST have a single root element !!! --}}
<div class="pregame">
    <header class="game-header">
        <a class="button button--type-text" href={{route('game-play.index')}}>Zurück zur Übersicht</a>
    </header>

    @if(!PreGameState::hasPlayerName($this->getGameEvents(), $myself))
        <h1>Trage Deinen Namen ein</h1>
        <x-pregame.selectName />
    @elseif(!PreGameState::hasPlayerLebensziel($this->getGameEvents(), $myself))
        <h1>Wähle Dein Lebensziel</h1>
        <x-pregame.selectLebensziel :lebensziele="$lebensziele" :lebensziel-form="$lebenszielForm" />
    @else
        <div class="pregame__start"
            @if(PreGameState::isReadyForGame($this->getGameEvents()))
                <button type="button" class="button button--type-primary" wire:click="startGame">Spiel starten</button>
            @else
                <button type="button" class="button button--type-primary" disabled="disabled">Warte auf andere Spieler...</button>
            @endif
        </div>
    @endif
</div>
