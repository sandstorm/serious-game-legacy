@props([
    'lebenszielPhase' => null,
    'phase' => 1,
])

<div class="lebensziel__phase">
    <p>{{$lebenszielPhase->description}}</p>

    <div class="lebensziel__phase-kompetenzen">
        @if($lebenszielPhase->investitionen->value > 0)
            <div>
                <h5>Für einen Phasenwechsel benötigst Du </h5>
                {!! $lebenszielPhase->investitionen->format() !!}
            </div>
        @endif
        <div>
            <h5>Diese Kompetenzen musst Du in Phase {{ $phase }} erwerben </h5>
            <div class="lebensziel__phase-kompetenzsteine">
                <div>
                    <span class="sr-only">Benötigte Kompetenzsteine: {{ $lebenszielPhase->bildungsKompetenzSlots }}</span>
                    <ul class="kompetenzen">
                        @for($i = 0; $i < $lebenszielPhase->bildungsKompetenzSlots; $i++)
                            <x-gameboard.kompetenzen.kompetenz-icon-bildung
                                :draw-empty="true" />
                        @endfor
                    </ul>
                    <span class="font-size--sm">Bildung & Karriere</span>
                </div>
                <div>
                    <span class="sr-only">Benötigte Kompetenzsteine: {{ $lebenszielPhase->freizeitKompetenzSlots }}</span>
                    <ul class="kompetenzen">
                        @for($i = 0; $i < $lebenszielPhase->freizeitKompetenzSlots; $i++)
                            <x-gameboard.kompetenzen.kompetenz-icon-freizeit
                                :draw-empty="true" />
                        @endfor
                    </ul>
                    <span class="font-size--sm">Freizeit & Soziales</span>
                </div>
            </div>
        </div>
    </div>

</div>
