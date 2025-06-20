@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')

@props([
    '$jobDefinition' => null,
    '$gameEvents' => null,
    '$playerId' => null,
])

<h3>Gehalt</h3>
@if ($jobDefinition)
    <table>
        <tbody>
        <tr>
            <td><small>Mein Job</small> <br /> {{ $jobDefinition->getTitle() }}</td>
            <td><small>Mein Gehalt</small> <br /> {{ $jobDefinition->gehalt->value }} €</td>
            <td><button type="button" class="button button--type-primary" wire:click="quitJob()">Job kündigen</button></td>
        </tr>
        </tbody>
    </table>
    <table>
        <tbody>
        <tr>
            <td>Du hast wegen deines Jobs weniger Zeit und kannst pro Jahr einen zeitstein weniger setzen. Bei Kündigung erhälst du diesen Zeitstein zurück.</td>
            <td>
                <ul class="zeitsteine">
                    <li>-{{ $jobDefinition->requirements->zeitsteine }}</li>
                    <li class="zeitsteine__item" @style(['background-color:' . PlayerState::getPlayerColor($gameEvents, $playerId)])></li>
                </ul>
            </td>
        </tr>
        </tbody>
    </table>
@else
    Arbeitslos und Spaß dabei!
@endif
