@use('Domain\CoreGameLogic\Feature\Spielzug\ValueObject\StockType')
@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\StockPriceState')

@props([
    'stocks' => []
])

<h3>Finanzen und Vermögenswerte</h3>
@if ($stocks)
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
    @foreach($stocks as $stock)
        <tr>
            <td>{{ $stock->amount }}</td>
            <td>{{ $stock->stockType->value }}</td>
            <td>{!! $stock->price->format() !!}</td>
            <td>
                @if ($stock->stockType === StockType::LOW_RISK)
                    1.20 € pro stück (TODO)
                @else
                    keine
                @endif
            </td>
            <td>
                Aktueller Preis: {!! StockPriceState::getCurrentStockPrice($gameEvents, $stock->stockType)->format() !!}
            </td>
        </tr>
    @endforeach
    <tr>
        <td colspan="4" class="text-align--right">Einnahmen Aktien gesamt</td>
        <td>TODO </td>
    </tr>
    </tbody>
</table>
@else
<p>Keine Aktien oder Immobilien vorhanden.</p>
@endif
