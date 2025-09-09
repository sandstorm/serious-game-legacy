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
    <p>Dein aktueller Kontostand: {!! PlayerState::getGuthabenForPlayer($gameEvents, $playerId)->formatWithIcon() !!}</p>
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
                        <td>{!! $investment->price->format() !!}</td>
                        <td>
                            <button
                                wire:click="showSellInvestmentOfTypeToAvoidInsolvenz('{{ $investment->investmentId }}')"
                                type="button"
                                @class([
                                    "button",
                                    "button--type-primary",
                                    "button--size-small",
                                    "button--disabled" => !$this->canSellInvestmentsToAvoidInsolvenz($investment->investmentId)->canExecute,
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
                                    action="sellInvestmentsToAvoidInsolvenz('{{ $this->sellInvestmentOfType }}')"
                                />
                            @endif
                        </td>
                    </tr>
                @endif
            @endforeach
            </tbody>
        </table>
    @else
        <h4>Keine Aktien oder Immobilien vorhanden.</h4>
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
