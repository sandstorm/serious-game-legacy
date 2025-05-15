@use('Domain\CoreGameLogic\Feature\Initialization\State\PreGameState')

<h3>PRE GAME</h3>

@foreach(PreGameState::playersWithNameAndLebensziel($this->gameStream()) as $nameAndLebensziel)
    @if($nameAndLebensziel->playerId->equals($myself))
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
                        <li wire:click="selectLebensZiel({{ $lebensziel->id }})" class="lebensziel-to-select @if($nameLebenszielForm->lebensziel == $lebensziel->id) lebensziel-to-select--is-selected @endif">
                            <x-lebensziel :lebensziel="$lebensziel" />
                        </li>
                    @endforeach
                </ul>
            </div>

            @if(!$nameAndLebensziel->hasNameAndLebensziel())
                <x-form.submit>Speichern</x-form.submit>
            @endif
        </form>
        {{$nameAndLebensziel->name}} - {{$nameAndLebensziel->lebensziel?->name}} <br/>
    @else
        SPIELER {{$nameAndLebensziel->playerId->value }}: {{$nameAndLebensziel->name}}
        - {{$nameAndLebensziel->lebensziel?->name}} <br/>
    @endif
@endforeach

@if(PreGameState::isReadyForGame($this->gameStream()))
    <button type="button" class="button button--type-primary" wire:click="startGame">EVERYTHING READY - START GAME</button>
@endif
