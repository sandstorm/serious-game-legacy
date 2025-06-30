@props([
    '$moneySheet' => null,
])

<h3>Steuern und Abgaben</h3>
<p>
    Dazu zählen Einkommensteuern, Sozialversicherung und Solidaritätszuschlag.
</p>

<form wire:submit="setSteuernUndAbgaben">
    <table>
        <tbody>
        <tr>
            <td><small>Dein Gehalt</small> <br /> {{ $moneySheet->gehalt->format() }} / Jahr</td>
            <td>
                <small>25% deines Gehalts</small> <br />

                <div class="form__group">
                    <x-form.textfield wire:model="moneySheetSteuernUndAbgabenForm.steuernUndAbgaben" id="steuernUndAbgaben" name="steuernUndAbgaben" type="number" step="0.01" min="0" :disabled="$this->moneySheetSteuernUndAbgabenForm->isSteuernUndAbgabenInputDisabled" />
                    @error('moneySheetSteuernUndAbgabenForm.steuernUndAbgaben') <span class="form__error">{{ $message }}</span> @enderror
                </div>

                <small>Pro Jahr gibst Du 25% Deines Gehaltes für Steuern und Abgaben aus.</small>
            </td>
        </tr>
        </tbody>
    </table>

    <x-form.submit :disabled="$this->moneySheetSteuernUndAbgabenForm->isSteuernUndAbgabenInputDisabled">Änderungen Speichern</x-form.submit>

    @if ($this->moneySheetSteuernUndAbgabenForm->isSteuernUndAbgabenInputDisabled)
        <p>Du hast deine Steuern und Abgaben erfolgreich eingetragen. Das Formular ist so lange deaktiviert bis sich an deinem Gehalt etwas ändert.</p>
    @endif
</form>
