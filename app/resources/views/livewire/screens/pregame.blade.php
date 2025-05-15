@use('Domain\CoreGameLogic\Feature\Initialization\State\PreGameState')

<h3>Vorbereitung des Spiels</h3>

@foreach(PreGameState::playersWithNameAndLebensziel($this->gameStream()) as $nameAndLebensziel)
    @if($nameAndLebensziel->playerId->equals($myself))
        @if(!$nameAndLebensziel->hasNameAndLebensziel())
            <form wire:submit="preGameSetNameAndLebensziel">
                <div class="form__group">
                    <label for="name">Dein Name:</label>
                    <x-form.textfield wire:model="nameLebenszielForm.name" id="name" name="name" type="text" />
                    @error('nameLebenszielForm.name') <span class="form__error">{{ $message }}</span> @enderror
                </div>
                <div class="form__group">
                    <h4>Wähle ein Lebensziel aus</h4>
                    @error('nameLebenszielForm.lebensziel') <span class="form__error">{{ $message }}</span> @enderror

                    <ul class="lebensziele-selector">
                        @foreach($lebensziele as $lebensziel)
                            <li class="lebensziel-to-select @if($nameLebenszielForm->lebensziel == $lebensziel->id) lebensziel-to-select--is-selected @endif">
                                <x-lebensziel :lebensziel="$lebensziel" />

                                @if($nameLebenszielForm->lebensziel != $lebensziel->id)
                                    <button type="button" class="button button--type-primary" wire:click="selectLebensZiel({{ $lebensziel->id }})">Dieses Lebensziel auswählen</button>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>

                <x-form.submit>Speichern</x-form.submit>
            </form>
        @else
            <div>
                Du hast folgendes Lebensziel ausgewählt:
                {{$nameAndLebensziel->name}} - {{$nameAndLebensziel->lebensziel?->name}}
            </div>
        @endif
    @else
        SPIELER {{$nameAndLebensziel->playerId->value }}: {{$nameAndLebensziel->name}}
        - {{$nameAndLebensziel->lebensziel?->name}} <br/>
    @endif
@endforeach

@if(PreGameState::isReadyForGame($this->gameStream()))
    <button type="button" class="button button--type-primary" wire:click="startGame">Spiel starten</button>
@endif
