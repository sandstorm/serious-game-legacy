@use('Domain\CoreGameLogic\Feature\Initialization\State\PreGameState')

<h3>PRE GAME</h3>

@foreach(PreGameState::playersWithNameAndLebensziel($this->gameStream()) as $nameAndLebensziel)
    @if($nameAndLebensziel->playerId->equals($myself))
        SPIELER (ICH)
        {{$nameAndLebensziel->playerId->value }}
        <form wire:submit="preGameSetNameAndLebensziel">
            <div class="form__group">
                <label for="name">Name:</label>
                <x-form.textfield wire:model="nameLebenszielForm.name" id="name" name="name" type="text" required="true" />
            </div>
            <div class="form__group">
                <label>Lebensziel:</label>
                <input class="form__textfield" type="text" wire:model="nameLebenszielForm.lebensziel">

                <h4>Lebensziele zur Auswahl</h4>
                <ul class="lebensziele">
                    <li class="lebensziel" wire:click="selectLebensZiel('Lebensziel ABC')">Lebensziel ABC</li>
                    <li class="lebensziel" wire:click="selectLebensZiel('Lebensziel XYZ')">Lebensziel XYZ</li>
                </ul>
            </div>

            @if(!$nameAndLebensziel->hasNameAndLebensziel())
                <x-form.submit>Speichern</x-form.submit>
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
