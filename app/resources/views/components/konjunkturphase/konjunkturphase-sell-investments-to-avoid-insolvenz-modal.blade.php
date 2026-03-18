@extends ('components.modal.modal', ['type' => "borderless"])
@use('Domain\Definitions\Investments\ValueObject\InvestmentId')
@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState')
@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')

@props([
    'investments' => [],
])

@section('icon')
    <i class="icon-phasenwechsel" aria-hidden="true"></i> Investitionen verkaufen
@endsection

@section('content')
    <p>Dein aktueller Kontostand: <x-money-amount :value="PlayerState::getGuthabenForPlayer($gameEvents, $playerId)" with-icon /></p>
    @if ($investments)
        <table>
            <thead>
            <tr>
                <th></th>
                <th>Anlageart</th>
                <th>Menge</th>
                <th>Aktueller Preis</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach($investments as $investment)
                @if ($investment->amount > 0)
                    <tr>
                        <td><i class="icon-aktien" aria-hidden="true"></i></td>
                        <td>{{ $investment->investmentId }}</td>
                        <td>{{ $investment->amount }}</td>
                        <td><x-money-amount :value="$investment->price" /></td>
                        <td>
                            <button
                                wire:click="showSellInvestmentOfTypeToAvoidInsolvenz('{{ $investment->investmentId }}')"
                                type="button"
                                @class([
                                    "button",
                                    "button--type-primary",
                                    "button--size-small",
                                    "button--disabled" => !$this->canSellInvestmentToAvoidInsolvenz($investment->investmentId)->canExecute,
                                    $this->getPlayerColorClass(),
                                ])
                            >
                                verkaufen
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5">
                            @if ($this->sellInvestmentOfType && $this->sellInvestmentOfType === $investment->investmentId)
                                <x-gameboard.investitionen.investitionen-sell-form
                                    :game-events="$gameEvents"
                                    action="sellInvestmentToAvoidInsolvenz('{{ $this->sellInvestmentOfType }}')"
                                />
                            @endif
                        </td>
                    </tr>
                @endif
            @endforeach
            </tbody>
        </table>
    @else
        <h4>Keine Investitionen vorhanden.</h4>
    @endif


@endsection

@section('footer')
    <button
        wire:click="toggleSellInvestmentsToAvoidInsolvenzModal()"
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
