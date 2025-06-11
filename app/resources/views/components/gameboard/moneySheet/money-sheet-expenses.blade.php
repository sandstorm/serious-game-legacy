@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')

@extends ('components.modal.modal', ['closeModal' => "toggleEditExpenses()", 'size' => 'large'])
@section('title')
    Money Sheet - Ausgaben
@endsection

@section('content')
    <div class="tabs">
        <ul role="tablist" class="tabs__list">
            <li @class(['tabs__list-item', 'tabs__list-item--active' => $this->activeTabForExpenses === 'credits'])>
                <button id="investments" type="button" class="button" role="tab" wire:click="$set('activeTabForExpenses', 'credits')">
                    Kredite
                </button>
            </li>
            <li @class(['tabs__list-item', 'tabs__list-item--active' => $this->activeTabForExpenses === 'kids'])>
                <button id="kids" type="button" class="button" role="tab" wire:click="$set('activeTabForExpenses', 'kids')">
                    Kinder
                </button>
            </li>
            <li @class(['tabs__list-item', 'tabs__list-item--active' => $this->activeTabForExpenses === 'insurances'])>
                <button id="insurances" type="button" class="button" role="tab" wire:click="$set('activeTabForExpenses', 'insurances')">
                    Versicherungen
                </button>
            </li>
            <li @class(['tabs__list-item', 'tabs__list-item--active' => $this->activeTabForExpenses === 'taxes'])>
                <button id="taxes" type="button" class="button" role="tab" wire:click="$set('activeTabForExpenses', 'taxes')">
                    Steuern und Abgaben
                </button>
            </li>
            <li @class(['tabs__list-item', 'tabs__list-item--active' => $this->activeTabForExpenses === 'livingCosts'])>
                <button id="livingCosts" type="button" class="button" role="tab" wire:click="$set('activeTabForExpenses', 'livingCosts')">
                    Lebenshaltungskosten
                </button>
            </li>
        </ul>

        @if ($this->activeTabForExpenses === 'credits')
            <div aria-labelledby="investments" role="tabpanel" class="tabs__tab">
                <h3>Kredite</h3>
                <table>
                    <thead>
                    <tr>
                        <th>Kreditverwendung</th>
                        <th>Kredithöhe</th>
                        <th>Rückzahlungssumme</th>
                        <th>Rückzahlung pro Runde</th>
                        <th>offene Raten</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>Kredit Phase 1</td>
                        <td>30.000 €</td>
                        <td>12.000 €</td>
                        <td>30.750 €</td>
                        <td>1.537,50 €</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-align--right">Einnahmen Aktien gesamt</td>
                        <td>0€</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        @elseif ($this->activeTabForExpenses === 'kids')
            <div aria-labelledby="kids" role="tabpanel" class="tabs__tab">
                <h3>Kinder</h3>
                <table>
                    <tbody>
                    <tr>
                        <td><small>Dein Brutto gehalt</small> <br /> {{ $moneySheet->gehalt }} €</td>
                        <td>
                            1.400 € <br />
                            <small>
                                Pro Jahr gibst Du 5% Deines Gehaltes pro Kind aus, jedoch mindestens 1.000 €.
                            </small>
                        </td>
                        <td><small>Anzahl Kinder</small> <br />0</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-align--right">Gesamtsumme</td>
                        <td>0 €</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        @elseif ($this->activeTabForExpenses === 'insurances')
            <div aria-labelledby="insurances" role="tabpanel" class="tabs__tab">
                <h3>Versicherungen</h3>
                <form>
                    <div class="form__group">
                        <label>
                            <input type="checkbox">
                            100€ /Jahr Haftpflichtversicherung
                        </label>
                    </div>
                    <div class="form__group">
                        <label>
                            <input type="checkbox">
                            150€ /Jahr Private Unfallversicherung
                        </label>
                    </div>
                    <div class="form__group">
                        <label>
                            <input type="checkbox">
                            Berufsunfähigkeitsversicherung (BU)
                        </label>
                        <small>
                            Phase I: 500€/Jahr <br />
                            Einstieg in Phase II: 700€/Jahr <br />
                            Einstieg in Phase III: 900€ /Jahr
                        </small>
                    </div>
                </form>
            </div>
        @elseif ($this->activeTabForExpenses === 'taxes')
            <div aria-labelledby="taxes" role="tabpanel" class="tabs__tab">
                <h3>Steuern und Abgaben</h3>
                <p>
                    Dazu zählen Einkommensteuern, Sozialversicherung und Solidaritätszuschlag.
                </p>
                <table>
                    <tbody>
                    <tr>
                        <td><small>Dein Gehalt</small> <br /> {{ $moneySheet->gehalt }} € / Jahr</td>
                        <td>
                            <small>25% deines Gehalts</small> <br />
                            {{ $moneySheet->steuernUndAbgaben }} € <br />
                            <small>Pro Jahr gibst Du 25% Deines Gehaltes für Steuern und Abgaben aus.</small>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        @elseif ($this->activeTabForExpenses === 'livingCosts')
            <div aria-labelledby="livingCosts" role="tabpanel" class="tabs__tab">
                <h3>Lebenshaltungskosten</h3>
                <p>
                    Dazu zählen Nahrung, Wohnen, Krankenversicherung, ...
                </p>
                <table>
                    <tbody>
                    <tr>
                        <td><small>Dein Gehalt</small> <br /> {{ $moneySheet->gehalt }} € / Jahr</td>
                        <td>
                            <small>35% deines Gehalts</small> <br />
                            {{ $moneySheet->lebenskosten }} € <br />
                            <small>Pro Jahr gibst Du 35% Deines Gehaltes für Lebenshaltungskosten aus. Jedoch mindestens 5.000 €</small>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection

@section('footer')
    <button type="button" class="button button--type-primary" wire:click="toggleEditExpenses()">Schließen</button>
@endsection
