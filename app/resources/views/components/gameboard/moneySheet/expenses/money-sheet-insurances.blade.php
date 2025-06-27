<h3>Versicherungen</h3>
<form wire:submit="setInsurances">
    <div class="form__group">
        @foreach ($this->moneySheetInsurancesForm->insurances as $key => $insurance)
            <label>
                <input type="checkbox" name="insurances[]" value="{{ $key }}" {{ $insurance['value'] ? 'checked' : '' }} wire:model="moneySheetInsurancesForm.insurances.{{ $key }}.value" />
                {{ $insurance['label'] }}
            </label>
        @endforeach
    </div>

    <x-form.submit disabled wire:dirty.remove.attr="disabled">Änderungen Speichern</x-form.submit>

    @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif
</form>
