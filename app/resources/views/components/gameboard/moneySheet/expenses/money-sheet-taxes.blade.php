@props([
    '$moneySheet' => null,
])

<h3>Steuern und Abgaben</h3>
<p>
    Dazu zählen Einkommensteuern, Sozialversicherung und Solidaritätszuschlag.
</p>
<table>
    <tbody>
    <tr>
        <td><small>Dein Gehalt</small> <br /> {{ $moneySheet->gehalt }} € / Jahr</td>
        <td>
            <small>25% deines Gehalts</small> <br />
            {{ $moneySheet->steuernUndAbgaben }} € <br />
            <small>Pro Jahr gibst Du 25% Deines Gehaltes für Steuern und Abgaben aus.</small>
        </td>
    </tr>
    </tbody>
</table>
