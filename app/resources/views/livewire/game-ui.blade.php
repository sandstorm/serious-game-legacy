@use('Domain\CoreGameLogic\Feature\Initialization\State\PreGameState')
<div>
    Game: {{ $gameId }}<br/>
    Ich bin Spieler: {{ PreGameState::nameForPlayer($this->gameStream(), $myself) }}<br/>

    @if(PreGameState::isInPreGamePhase($this->gameStream()))
        PRE GAME<br/>

        @foreach(PreGameState::playersWithNameAndLebensziel($this->gameStream()) as $nameAndLebensziel)
            @if($nameAndLebensziel->playerId->equals($myself))
                SPIELER (ICH)
                {{$nameAndLebensziel->playerId->value }}
                <form wire:submit="preGameSetNameAndLebensziel">
                    NAME: <input type="text" wire:model="nameLebenszielForm.name"
                                 :disabled={{ $nameAndLebensziel->hasNameAndLebensziel() }}><br/>
                    Lebensziel: <input type="text" wire:model="nameLebenszielForm.lebensziel"
                                       :disabled={{$nameAndLebensziel->hasNameAndLebensziel()}}> (TODO SELECT)<br/>

                    @if(!$nameAndLebensziel->hasNameAndLebensziel())
                        <button type="submit">Save</button>
                    @endif
                </form>
                {{$nameAndLebensziel->name}} - {{$nameAndLebensziel->lebensziel?->value}} <br/>
            @else
                SPIELER {{$nameAndLebensziel->playerId->value }}: {{$nameAndLebensziel->name}}
                - {{$nameAndLebensziel->lebensziel?->value}} <br/>
            @endif
        @endforeach

        @if(PreGameState::isReadyForGame($this->gameStream()))
            <a wire:click="startGame">EVERYTHING READY - START GAME</a>
        @endif
    @else

        GAME

        <br/>
        <br/>

        Aktueller Spieler: {{ PreGameState::nameForPlayer($this->gameStream(), $this->currentPlayer()) }}<br/>
        <button type="button" wire:click="triggerGameAction('foo')">Game Action Foo</button><br/>
        <button type="button" wire:click="spielzugAbschliessen()">Spielzug abschließen</button>
    @endif
</div>
