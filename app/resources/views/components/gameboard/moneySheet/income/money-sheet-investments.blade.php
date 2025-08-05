@use('Domain\CoreGameLogic\Feature\Spielzug\ValueObject\StockType')
@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState')
@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')

@props([
    'stocks' => []
])

<div class="tabs__upper-content">
    @if ($stocks)
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
            @foreach($stocks as $stock)
                <tr>
                    <td><i class="icon-aktien" aria-hidden="true"></i></td>
                    <td>{{ $stock->stockType->toPrettyString() }}</td>
                    <td>{{ $stock->amount }}</td>
                    <td>{!! $stock->price->format() !!}</td>
                    <td>
                        @if ($stock->stockType === StockType::LOW_RISK)
                            {!! KonjunkturphaseState::getCurrentKonjunkturphase($gameEvents)->getDividend()->format() !!}
                        @else
                            keine
                        @endif
                    </td>
                    <td>
                        {!! $stock->totalValue->formatWithIcon() !!}
                    </td>
                </tr>
            @endforeach
            <tr>
                <td colspan="5" class="text-align--right">Einnahmen Aktien gesamt</td>
                <td>{!! PlayerState::getTotalValueOfAllStocksForPlayer($gameEvents, $playerId)->formatWithIcon() !!}</td>
            </tr>
            </tbody>
        </table>
    @else
        <h4>Keine Aktien oder Immobilien vorhanden.</h4>
    @endif

</div>
