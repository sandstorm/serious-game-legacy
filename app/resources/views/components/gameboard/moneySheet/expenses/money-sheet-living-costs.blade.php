@props([
    '$moneySheet' => null,
])

<h3>Lebenshaltungskosten</h3>
<p>
    Dazu zählen Nahrung, Wohnen, Krankenversicherung, ...
</p>
<form wire:submit="setLebenskosten">
    <table>
        <tbody>
        <tr>
            <td><small>Dein Gehalt</small> <br /> {{ $moneySheet->gehalt }} € / Jahr</td>
            <td>
                <small>35% deines Gehalts</small> <br />

                <div class="form__group">
                    <x-form.textfield wire:model="moneySheetLebenskostenForm.lebenskosten" id="lebenskosten" name="lebenskosten" type="number" step="0.01" min="5000" :disabled="$this->moneySheetLebenskostenForm->lebenskostenIsDisabled" />
                    @error('moneySheetLebenskostenForm.lebenskosten') <span class="form__error">{{ $message }}</span> @enderror
                </div>

                <small>Pro Jahr gibst Du 35% Deines Gehaltes für Lebenshaltungskosten aus. Jedoch mindestens 5.000 €</small>
            </td>
        </tr>
        </tbody>
    </table>

    <x-form.submit :disabled="$this->moneySheetLebenskostenForm->lebenskostenIsDisabled">Änderungen Speichern</x-form.submit>

    @if ($this->moneySheetLebenskostenForm->lebenskostenIsDisabled)
        <p>Du hast deine Lebenshaltungskosten erfolgreich eingetragen. Das Formular ist so lange deaktiviert bis sich an deinem Gehalt etwas ändert.</p>
    @endif

    @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif
</form>
