@props([
    '$moneySheet' => null,
])

<h3>Lebenshaltungskosten</h3>
<p>
    Dazu zählen Nahrung, Wohnen, Krankenversicherung, ...
</p>
<form wire:submit="setLebenshaltungskosten">
    <table>
        <tbody>
        <tr>
            <td><small>Dein Gehalt</small> <br /> {{ $moneySheet->gehalt->format() }} / Jahr</td>
            <td>
                <small>35% deines Gehalts</small> <br />

                <div class="form__group">
                    <x-form.textfield wire:model="moneySheetLebenshaltungskostenForm.lebenshaltungskosten" id="lebenshaltungskosten" name="lebenshaltungskosten" type="number" step="0.01" :disabled="$this->moneySheetLebenshaltungskostenForm->isLebenshaltungskostenInputDisabled" />
                    @error('moneySheetLebenshaltungskostenForm.lebenshaltungskosten') <span class="form__error">{{ $message }}</span> @enderror
                </div>

                <small>Pro Jahr gibst Du 35% Deines Gehaltes für Lebenshaltungskosten aus. Jedoch mindestens 5.000 €</small>
            </td>
        </tr>
        </tbody>
    </table>

    <x-form.submit :disabled="$this->moneySheetLebenshaltungskostenForm->isLebenshaltungskostenInputDisabled">Änderungen Speichern</x-form.submit>

    @if ($this->moneySheetLebenshaltungskostenForm->isLebenshaltungskostenInputDisabled)
        <p>Du hast deine Lebenshaltungskosten erfolgreich eingetragen. Das Formular ist so lange deaktiviert bis sich an deinem Gehalt etwas ändert.</p>
    @endif
</form>
