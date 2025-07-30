@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')

@props([
    '$jobDefinition' => null,
    '$gameEvents' => null,
    '$playerId' => null,
    '$modifiers' => null,
    '$gehalt' => null,
])

<h3>Gehalt</h3>
@if ($jobDefinition)
    <table>
        <tbody>
        <tr>
            <td><small>Mein Job</small> <br/> {{ $jobDefinition->getTitle() }}</td>
            <td>
                <small>Mein Gehalt</small> <br/>
                {!! $gehalt->format() !!}
                @if(!$gehalt->equals($jobDefinition->getGehalt()))
                    (Basisgehalt: {!! $jobDefinition->getGehalt()->format() !!})
                @endif
            </td>
            <td>
                <button type="button" class="button button--type-primary" wire:click="quitJob()">Job kündigen</button>
            </td>
        </tr>
        </tbody>
    </table>
    <table>
        <tbody>
        @foreach($modifiers as $modifier)
            <tr>
                <td>{{$modifier}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <p>Du hast gerade keinen Job.</p>
@endif
