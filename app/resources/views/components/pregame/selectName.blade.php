<form class="pregame__select-name" wire:submit="preGameSetName">
    <div class="form__group">
        <label for="name" class="sr-only">Dein Name:</label>
        <x-form.textfield wire:model="nameForm.name" id="name" name="name" type="text"/>
        @error('nameForm.name') <span class="form__error">{{ $message }}</span> @enderror
    </div>

    <x-form.submit>Weiter</x-form.submit>
</form>
