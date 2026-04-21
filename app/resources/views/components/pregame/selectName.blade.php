@use('Domain\Definitions\PlayerRole\PlayerRole')

<form class="pregame__select-name" wire:submit="preGameSetName">
    <div class="form-group">
        <label for="name" class="sr-only">Dein Name:</label>
        <x-form.textfield wire:model="nameForm.name" id="name" name="name" type="text"/>
        @error('nameForm.name') <span class="form-error">{{ $message }}</span> @enderror
    </div>

    @if ($this->showRoleSelection)
        <div class="form-group">
            <label for="role" class="sr-only">Deine Rolle:</label>
            <select id="role" name="role" class="form-group__input" wire:model="nameForm.role">
                <option value="">-- Bitte Rolle auswählen --</option>
                @foreach (PlayerRole::cases() as $playerRole)
                    <option value="{{ $playerRole->value }}">{{ $playerRole->value }}</option>
                @endforeach
            </select>
            @error('nameForm.role') <span class="form-error">{{ $message }}</span> @enderror
        </div>
    @endif

    <x-form.submit>Weiter</x-form.submit>
</form>
