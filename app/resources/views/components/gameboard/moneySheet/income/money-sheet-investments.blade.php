@props([
    'stocks' => []
])

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
    @foreach($stocks as $stock)
        <tr>
            <td>{{ $stock->amount }}</td>
            <td>{{ $stock->stockType->value }}</td>
            <td>{!! $stock->price->format() !!}</td>
            <td>todo</td>
            <td>todo</td>
        </tr>
    @endforeach
    <tr>
        <td colspan="4" class="text-align--right">Einnahmen Aktien gesamt</td>
        <td>TODO </td>
    </tr>
    </tbody>
</table>
