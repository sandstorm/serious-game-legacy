@props([
    'lebensziele' => [],
    'lebenszielForm' => null,
])
<form class="pregame__select-lebensziel" wire:submit="preGameSetLebensziel">
    <div class="form-group">
        <label class="sr-only" for="lebensziel">Lebensziele</label>
        <select id="lebensziel" name="lebensziel" class="form-group__input" wire:model="lebenszielForm.lebensziel" wire:change="selectLebensZiel($event.target.value)">
            <option value="" disabled>-- Wähle Dein Lebensziel --</option>
            @foreach($lebensziele as $lebensziel)
                <option value="{{ $lebensziel->id->value }}">{{ $lebensziel->name }}</option>
            @endforeach
        </select>
    </div>

    @foreach($lebensziele as $lebensziel)
        @if ($lebenszielForm->lebensziel == $lebensziel->id->value)
            <div>
                <strong>Dein Lebensziel: </strong> {{ $lebensziel->name }}
            </div>
            <x-lebensziel.lebensziel-phase-preview :lebensziel-phase="$lebensziel->phaseDefinitions[0]" :phase="1" />
        @endif
    @endforeach

    @error('lebenszielForm.lebensziel') <span class="form__error">{{ $message }}</span> @enderror
    <x-form.submit>Weiter</x-form.submit>
</form>
