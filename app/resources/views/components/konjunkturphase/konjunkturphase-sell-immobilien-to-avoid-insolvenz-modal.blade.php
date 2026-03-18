@extends ('components.modal.modal', ['type' => "borderless"])
@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\ImmobilienPriceState')
@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')

@props([
    'immobilienOwnedByPlayer' => [],
])

@section('icon')
    <i class="icon-phasenwechsel" aria-hidden="true"></i> Immobilien verkaufen
@endsection

@section('content')
    <p>Dein aktueller Kontostand: <x-money-amount :value="PlayerState::getGuthabenForPlayer($gameEvents, $playerId)" with-icon /></p>
    @if ($immobilienOwnedByPlayer)
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
                    <td><x-money-amount :value="$immobilieOwnedByPlayer->getPurchasePrice()" /></td>
                    <td><x-money-amount :value="$immobilieOwnedByPlayer->getAnnualRent()" /></td>
                    <td><x-money-amount :value="ImmobilienPriceState::getCurrentPriceForImmobilie($gameEvents, $immobilieOwnedByPlayer->getImmobilieId())" with-icon /></td>
                    <td>
                        <button
                            type="button"
                            @class([
                                "button",
                                "button--type-primary",
                                "button--size-small",
                                $this->getPlayerColorClass(),
                                "button--disabled" => !$this->canSellImmobilieToAvoidInsolvenz($immobilieOwnedByPlayer->getImmobilieId())->canExecute,
                            ])
                            wire:click="sellImmobilieToAvoidInsolvenz('{{ $immobilieOwnedByPlayer->getImmobilieId() }}')"
                        >
                            Verkaufen
                        </button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <h4>Keine Immobilien vorhanden.</h4>
    @endif
@endsection

@section('footer')
    <button
        wire:click="toggleSellImmobilienToAvoidInsolvenzModal()"
        type="button"
        @class([
            "button",
            "button--type-primary",
            $this->getPlayerColorClass(),
        ])
    >
        zurück
    </button>
@endsection
