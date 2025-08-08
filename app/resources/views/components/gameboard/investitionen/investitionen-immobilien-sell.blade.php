@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\ImmobilienPriceState')

@props([
    'immobilienOwnedByPlayer' => [],
    'gameEvents' => null,
])

@if (count($immobilienOwnedByPlayer) > 0)
    <table>
        <thead>
        <tr>
            <th></th>
            <th>Titel</th>
            <th>Kaufpreis</th>
            <th>Mietertrag</th>
            <th>Verkaufspreis</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach($immobilienOwnedByPlayer as $immobilieOwnedByPlayer)
            <tr>
                <td><i class="icon-immobilien" aria-hidden="true"></i></td>
                <td>{{ $immobilieOwnedByPlayer->getTitle() }}</td>
                <td>{!! $immobilieOwnedByPlayer->getPurchasePrice()->format() !!}</td>
                <td>{!! $immobilieOwnedByPlayer->getAnnualRent()->format() !!}</td>
                <td>{!! ImmobilienPriceState::getCurrentPriceForImmobilie($gameEvents, $immobilieOwnedByPlayer->getImmobilieId())->formatWithIcon() !!}</td>
                <td>
                    <button
                        type="button"
                        @class([
                            "button",
                            "button--type-primary",
                            "button--size-small",
                            $this->getPlayerColorClass(),
                            "button--disabled" => !$this->canSellImmobilie($immobilieOwnedByPlayer->getImmobilieId())->canExecute,
                        ])
                        wire:click="sellImmobilie('{{ $immobilieOwnedByPlayer->getImmobilieId() }}')"
                    >
                        Verkaufen
                        <div class="button__suffix">
                            <div>
                                <i class="icon-minus text--danger" aria-hidden="true"></i><i class="icon-zeitstein"
                                                                                             aria-hidden="true"></i>
                                <span class="sr-only">, kostet einen Zeitstein</span>
                            </div>
                        </div>
                    </button>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <h4>Keine Immobilien zum Verkauf vorhanden.</h4>
@endif
