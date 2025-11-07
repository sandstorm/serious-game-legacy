@props([
    'lebensziel' => null,
])

<div class="lebensziel">
    <strong>Lebensziel:</strong> {{ $lebensziel->name }}

    <div class="lebensziel__phasen">
        @foreach($lebensziel->phaseDefinitions as $phase)
            <h4>Phase {{$lebenszielPhase->lebenszielPhaseId->value}}</h4>
            <x-lebensziel.lebensziel-phase-preview :lebensziel-phase="$phase" :phase="$loop->index + 1" />
        @endforeach
    </div>
</div>
