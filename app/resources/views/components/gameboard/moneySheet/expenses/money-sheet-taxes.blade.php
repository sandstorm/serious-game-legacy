@props([
    'moneySheet' => null,
])

<form wire:submit="setSteuernUndAbgaben">
    <div class="tabs__upper-content">
        <p>
            Zu den Steuern zählen Einkommensteuern, Sozialversicherung und Solidaritätszuschlag. <br />
            Pro Jahr gibst Du 25% Deines Jahreseinkommens brutto für Steuern und Abgaben aus.
        </p>

        <div class="taxes">
            <div class="form-group">
                <span class="form-group__label">Dein Jahreseinkommen brutto</span>
                <div class="form-group__input">
                    <i class="icon-erwerbseinkommen"></i>
                    {!! $moneySheet->gehalt->format() !!}
                </div>
            </div>

            <div class="form-group">
                <label class="form-group__label" for="steuernUndAbgaben">25% Deines Gehalts</label>
                <x-form.textfield wire:model="moneySheetSteuernUndAbgabenForm.steuernUndAbgaben" id="steuernUndAbgaben" name="steuernUndAbgaben" type="number" step="0.01" min="0" :disabled="$this->moneySheetSteuernUndAbgabenForm->isSteuernUndAbgabenInputDisabled" />
            </div>
        </div>
    </div>
    <div class="tabs__lower-content taxes__actions">
        @error('moneySheetSteuernUndAbgabenForm.steuernUndAbgaben') <span class="form__error badge-with-background">{{ $message }}</span> @enderror
        @if ($this->moneySheetSteuernUndAbgabenForm->isSteuernUndAbgabenInputDisabled)
            <span class="text--success badge-with-background">
                <i class="icon-info-2" aria-hidden="true"></i> Deine Steuern und Abgaben sind erfolgreich eingetragen. Das Formular ist so lange deaktiviert bis sich an deinem Gehalt etwas ändert.
            </span>
        @endif
        <x-form.submit :disabled="$this->moneySheetSteuernUndAbgabenForm->isSteuernUndAbgabenInputDisabled">Änderungen speichern</x-form.submit>
    </div>
</form>

