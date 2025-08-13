@use('Domain\Definitions\Investments\ValueObject\InvestmentId')
@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState')
@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')

@props([
    'investments' => []
])

<div class="tabs__upper-content">
    @if ($investments)
        <table>
            <thead>
            <tr>
                <th></th>
                <th>Anlageart</th>
                <th>Menge</th>
                <th>Aktueller Preis</th>
                <th>Dividende/Stück oder Mietertrag</th>
                <th>Einnahmen</th>
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
                        @if ($investment->investmentId === InvestmentId::MERFEDES_PENZ)
                            {!! KonjunkturphaseState::getCurrentKonjunkturphase($gameEvents)->getDividend()->format() !!}
                        @else
                            keine
                        @endif
                    </td>
                    <td>
                        {!! $investment->totalDividend->formatWithIcon() !!}
                    </td>
                </tr>
                @endif
            @endforeach
            <tr>
                <td colspan="5" class="text-align--right">Einnahmen Aktien gesamt</td>
                <td>{!! PlayerState::getDividendForAllStocksForPlayer($gameEvents, $playerId)->formatWithIcon() !!}</td>
            </tr>
            </tbody>
        </table>
    @else
        <h4>Keine Aktien oder Immobilien vorhanden.</h4>
    @endif
</div>
