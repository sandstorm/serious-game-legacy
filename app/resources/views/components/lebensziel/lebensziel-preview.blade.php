@props([
    'lebensziel' => null,
])

<div class="lebensziel">
    <strong>Lebensziel:</strong> {{ $lebensziel->name }}

    <div class="lebensziel__phasen">
        @foreach($lebensziel->phaseDefinitions as $phase)
            <div class="lebensziel__phase">
                <h4>Phase {{$phase->lebenszielPhaseId->value}}</h4>
                <p>{{$phase->description}}</p>

                <h5>Bildung & Karriere</h5>
                <span class="sr-only">Benötigte Kompetenzsteine: {{ $phase->bildungsKompetenzSlots }}</span>
                <ul class="kompetenzen">
                    @for($i = 0; $i < $phase->bildungsKompetenzSlots; $i++)
                        <x-gameboard.kompetenzen.kompetenz-icon-bildung
                            :draw-empty="true" />
                    @endfor
                </ul>

                <h5>Freizeit & Soziales</h5>
                <span class="sr-only">Benötigte Kompetenzsteine: {{ $phase->freizeitKompetenzSlots }}</span>
                <ul class="kompetenzen">
                    @for($i = 0; $i < $phase->freizeitKompetenzSlots; $i++)
                        <x-gameboard.kompetenzen.kompetenz-icon-freizeit
                            :draw-empty="true" />
                    @endfor
                </ul>

                @if($phase->investitionen->value > 0)
                    <div>
                        <strong>Investitionen: </strong> {!! $phase->investitionen->format() !!}
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
