@props([
    'totalCost' => null,
])

<form class="insurances" wire:submit="setInsurances">
    <fieldset class="tabs__upper-content form-group">
        <legend class="sr-only">Auswahl an Versicherungen: </legend>
        @foreach ($this->moneySheetInsurancesForm->insurances as $key => $insurance)
            <label @class(["switch", $this->getPlayerColorClass()])>
                <input type="checkbox" name="insurances[]" value="{{ $key }}" {{ $insurance['value'] ? 'checked' : '' }} wire:model="moneySheetInsurancesForm.insurances.{{ $key }}.value" />
                <div class="slider"></div>
                <div>
                    <strong>{{ $insurance['label'] }}</strong> <br />
                    <x-money-amount :value="new \Domain\Definitions\Card\ValueObject\MoneyAmount($insurance['annualCost'])" /> / Jahr
                </div>
            </label>
        @endforeach
    </fieldset>

    <div class="tabs__lower-content insurances__actions">
        <div class="insurances__total-cost">
            <x-money-amount :value="$totalCost" with-icon />
            <span>Summe Versicherungen</span>
        </div>
        <x-form.submit disabled wire:dirty.remove.attr="disabled">Änderungen speichern</x-form.submit>
    </div>
</form>

