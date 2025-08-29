@props([
    'moneySheet' => null,
])

<form wire:submit="setLebenshaltungskosten">
    <div class="tabs__upper-content">
        <p>
            Dazu zählen Nahrung, Wohnen, Krankenversicherung, ... <br />
            Pro Jahr gibst Du <strong>{{$livingCostPercent}}%</strong> Deines Gehaltes für Lebenshaltungskosten aus. Jedoch mindestens <strong>{!! $livingCostMinValue->format() !!}</strong>
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
                <label class="form-group__label" for="steuernUndAbgaben">{{$livingCostPercent}}% Deines Gehalts</label>
                <x-form.textfield wire:model="moneySheetLebenshaltungskostenForm.lebenshaltungskosten" id="lebenshaltungskosten" name="lebenshaltungskosten" type="number" step="0.01" :disabled="$this->moneySheetLebenshaltungskostenForm->isLebenshaltungskostenInputDisabled" />
            </div>
        </div>
        @if ($modifiers)
            <hr />
            <table>
                <tbody>
                @foreach($modifiers as $modifier)
                    <tr>
                        <td>{{$modifier}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="tabs__lower-content taxes__actions">
        @error('moneySheetLebenshaltungskostenForm.lebenshaltungskosten') <span class="form__error badge-with-background">{{ $message }}</span> @enderror
        @if ($this->moneySheetLebenshaltungskostenForm->isLebenshaltungskostenInputDisabled)
            <span class="text--success badge-with-background"><i class="icon-info-2" aria-hidden="true"></i> Deine Lebenshaltungskosten sind erfolgreich eingetragen. Das Formular ist so lange deaktiviert bis sich an deinem Gehalt etwas ändert.</span>
        @endif
        <x-form.submit :disabled="$this->moneySheetLebenshaltungskostenForm->isLebenshaltungskostenInputDisabled">Änderungen speichern</x-form.submit>
    </div>
</form>
