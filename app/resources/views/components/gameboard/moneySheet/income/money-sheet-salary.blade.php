@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')

@props([
    'jobDefinition' => null,
    'zeitsteinModifiers' => null,
    'gehaltModifiers' => null,
    'currentGehalt' => null,
])

<div class="tabs__upper-content">
    <div class="salary">
        @if ($jobDefinition)
            <table>
                <tbody>
                <tr>
                    <td><i class="icon-erwerbseinkommen" aria-hidden="true"></i></td>
                    <td>
                        <small>Dein aktueller Job</small> <br/>
                        <div class="salary__job-title">{{ $jobDefinition->getTitle() }}</div>
                    </td>
                    <td>
                        <button
                            type="button"
                            wire:click="quitJob()"
                            @class([
                                'button',
                                'button--type-primary',
                                $this->getPlayerColorClass(),
                            ])
                        >
                            Job kündigen
                        </button>
                    </td>
                    <td class="text-align--right">
                        <small>Dein Jahreseinkommen brutto</small> <br/>
                        {!! $jobDefinition->getGehalt()->formatWithIcon() !!}
                    </td>
                </tr>
                <tr>
                    <td colspan="4"><hr /></td>
                </tr>
                @foreach($zeitsteinModifiers as $modifier)
                    <tr>
                        <td>
                            <x-gameboard.zeitsteine.zeitstein-icon :player-color-class="$this->getPlayerColorClass()" />
                        </td>
                        <td colspan="3">{{$modifier->description}}</td>
                    </tr>
                @endforeach
                @foreach($gehaltModifiers as $modifier)
                    <tr>
                        <td><i class="icon-ereignis" aria-hidden="true"></i></td>
                        <td colspan="3">{{$modifier->description}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <h4><i class="icon-erwerbseinkommen" aria-hidden="true"></i> Du hast momentan kein regelmäßiges Einkommen. </h4>
        @endif
    </div>
</div>

@if ($currentGehalt->value > 0)
    <div class="tabs__lower-content">
        <div class="salary__summary">
            {!! $currentGehalt->formatWithIcon() !!}
        </div>
    </div>
@endif
