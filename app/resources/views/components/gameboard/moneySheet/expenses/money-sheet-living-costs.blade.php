@props([
    'moneySheet' => null,
])

<form wire:submit="setLebenshaltungskosten">
    <div class="tabs__upper-content">
        <p>
            Dazu zählen Nahrung, Wohnen, Krankenversicherung, ... <br />
            Pro Jahr gibst Du {{$livingCostPercent}}% Deines Gehaltes für Lebenshaltungskosten aus. Jedoch mindestens {!! $livingCostMinValue->format() !!}
        </p>

        @if ($this->moneySheetLebenshaltungskostenForm->isLebenshaltungskostenInputDisabled)
            <br />
            <p><i class="icon-info" aria-hidden="true"></i> Du hast deine Lebenshaltungskosten erfolgreich eingetragen. Das Formular ist so lange deaktiviert bis sich an deinem Gehalt etwas ändert.</p>
        @endif

        <div class="taxes">
            <div class="form-group">
                <span class="form-group__label">Dein Jahreseinkommen brutto</span>
                <div class="form-group__input">
                    <i class="icon-erwerbseinkommen"></i>
                    {!! $moneySheet->gehalt->format() !!}
                </div>
            </div>

            <div class="form-group">
                <label class="form-group__label" for="steuernUndAbgaben">{{$livingCostPercent}}% Deines Gehalts</label>
                <x-form.textfield wire:model="moneySheetLebenshaltungskostenForm.lebenshaltungskosten" id="lebenshaltungskosten" name="lebenshaltungskosten" type="number" step="0.01" :disabled="$this->moneySheetLebenshaltungskostenForm->isLebenshaltungskostenInputDisabled" />
            </div>
            <table>
                <tbody>
                @foreach($modifiers as $modifier)
                    <tr>
                        <td>{{$modifier}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="tabs__lower-content taxes__actions">
        @error('moneySheetLebenshaltungskostenForm.lebenshaltungskosten') <span class="form__error">{{ $message }}</span> @enderror
        <x-form.submit :disabled="$this->moneySheetLebenshaltungskostenForm->isLebenshaltungskostenInputDisabled">Änderungen Speichern</x-form.submit>
    </div>
</form>
