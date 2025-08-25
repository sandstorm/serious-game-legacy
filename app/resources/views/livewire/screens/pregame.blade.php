@use('Domain\CoreGameLogic\Feature\Initialization\State\PreGameState')

{{-- !!! Livewire components MUST have a single root element !!! --}}
<div>
    <h2>Spiel ID: {{ $gameId }}</h2>
    <h3>Vorbereitung des Spiels</h3>

    @foreach(PreGameState::playersWithNameAndLebensziel($this->getGameEvents()) as $nameAndLebensziel)
        @if($nameAndLebensziel->playerId->equals($myself))
            @if(!$nameAndLebensziel->hasNameAndLebensziel())
                <form wire:submit="preGameSetNameAndLebensziel">
                    <div class="form__group">
                        <label for="name">Dein Name:</label>
                        <x-form.textfield wire:model="nameLebenszielForm.name" id="name" name="name" type="text"/>
                        @error('nameLebenszielForm.name') <span class="form__error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form__group">
                        <h4>Wähle ein Lebensziel aus</h4>
                        @error('nameLebenszielForm.lebensziel') <span
                                class="form__error">{{ $message }}</span> @enderror

                        <ul class="lebensziele-selector">
                            @foreach($lebensziele as $lebensziel)
                                <li @class([
                                    'lebensziel-to-select',
                                    'lebensziel-to-select--is-selected' => $nameLebenszielForm->lebensziel == $lebensziel->id->value
                                ])>
                                    <x-lebensziel.lebensziel-preview :lebensziel="$lebensziel"/>
                                    <button type="button" class="button button--type-primary"
                                            wire:click="selectLebensZiel({{ $lebensziel->id->value }})">
                                        @if($nameLebenszielForm->lebensziel != $lebensziel->id->value)
                                            Dieses Lebensziel auswählen
                                        @else
                                            Lebensziel ausgewählt
                                        @endif
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <x-form.submit>Speichern</x-form.submit>
                </form>
            @else
                <div>
                    Deine ID: {{$nameAndLebensziel->playerId->value }}<br/>
                    Du hast folgendes Lebensziel ausgewählt:
                    {{$nameAndLebensziel->name}} - {{$nameAndLebensziel->lebensziel?->name}}
                </div>
                <hr/>
            @endif
        @else
            <div>
                Spieler {{$nameAndLebensziel->playerId->value }}: {{$nameAndLebensziel->name}}
                - {{$nameAndLebensziel->lebensziel?->name}}
            </div>
            <hr/>
        @endif
    @endforeach
    @if(PreGameState::isReadyForGame($this->getGameEvents()))
        <button type="button" class="button button--type-primary" wire:click="startGame">Spiel starten</button>
    @endif
</div>
