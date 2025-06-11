@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')

@extends ('components.modal.modal', ['closeModal' => "toggleEditIncome()", 'size' => 'large'])
@section('title')
    Money Sheet - Einnahmen
@endsection

@section('content')
    <div class="tabs">
        <ul role="tablist" class="tabs__list">
            <li @class(['tabs__list-item', 'tabs__list-item--active' => $this->activeTabForIncome === 'investments'])>
                <button id="investments" type="button" class="button" role="tab" wire:click="$set('activeTabForIncome', 'investments')">
                    Finanzanlagen und Vermögenswerte
                </button>
            </li>
            <li @class(['tabs__list-item', 'tabs__list-item--active' => $this->activeTabForIncome === 'salary'])>
                <button id="salary" type="button" class="button" role="tab" wire:click="$set('activeTabForIncome', 'salary')">
                    Gehalt
                </button>
            </li>
        </ul>

        @if ($this->activeTabForIncome === 'investments')
            <div aria-labelledby="investments" role="tabpanel" class="tabs__tab">
                <h3>Finanzen und Vermögenswerte</h3>
                <table>
                    <thead>
                    <tr>
                        <th>Menge</th>
                        <th>Beschreibung</th>
                        <th>Kaufpreis/Stück</th>
                        <th>Dividende oder Mietertrag/Stück</th>
                        <th>Einnahmen</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>1.000</td>
                        <td>Aktie XYZ</td>
                        <td>12.000 €</td>
                        <td>0 €</td>
                        <td>0 €</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-align--right">Einnahmen Aktien gesamt</td>
                        <td>0 €</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        @elseif ($this->activeTabForIncome === 'salary')
            <div aria-labelledby="salary" role="tabpanel" class="tabs__tab">
                <h3>Gehalt</h3>
                @if ($jobDefinition)
                    <table>
                        <tbody>
                        <tr>
                            <td><small>Mein Job</small> <br /> {{ $jobDefinition->getTitle() }}</td>
                            <td><small>Mein Gehalt</small> <br /> {{ $jobDefinition->gehalt->value }} €</td>
                            <td><button type="button" class="button button--type-primary" wire:click="quitJob()">Job kündigen</button></td>
                        </tr>
                        </tbody>
                    </table>
                    <table>
                        <tbody>
                        <tr>
                            <td>Du hast wegen deines Jobs weniger Zeit und kannst pro Jahr einen zeitstein weniger setzen. Bei Kündigung erhälst du diesen Zeitstein zurück.</td>
                            <td>
                                <ul class="zeitsteine">
                                    <li>-{{ $jobDefinition->requirements->zeitsteine }}</li>
                                    <li class="zeitsteine__item" @style(['background-color:' . PlayerState::getPlayerColor($gameStream, $playerId)])></li>
                                </ul>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                @else
                    Arbeitslos und Spaß dabei!
                @endif
            </div>
        @endif
    </div>
@endsection

@section('footer')
    <button type="button" class="button button--type-primary" wire:click="toggleEditIncome()">Schließen</button>
@endsection
