@props([
    'gehalt' => 0,
])

<div class="tabs__upper-content">
    <table>
        <tbody>
        <tr>
            <td><small>Dein Brutto gehalt</small> <br /> {!! $gehalt->format() !!}</td>
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

