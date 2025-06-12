@props([
    '$moneySheet' => null,
])

<h3>Lebenshaltungskosten</h3>
<p>
    Dazu zählen Nahrung, Wohnen, Krankenversicherung, ...
</p>
<table>
    <tbody>
    <tr>
        <td><small>Dein Gehalt</small> <br /> {{ $moneySheet->gehalt }} € / Jahr</td>
        <td>
            <small>35% deines Gehalts</small> <br />
            {{ $moneySheet->lebenskosten }} € <br />
            <small>Pro Jahr gibst Du 35% Deines Gehaltes für Lebenshaltungskosten aus. Jedoch mindestens 5.000 €</small>
        </td>
    </tr>
    </tbody>
</table>
