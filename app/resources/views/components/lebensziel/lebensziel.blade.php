@props(['lebensziel' => null])

<div class="lebensziel">
    <header>
        Lebensziel: {{$lebensziel->name}}
    </header>

    <ul class="lebensziel__phasen">
        @foreach($lebensziel->phaseDefinitions as $phase)
            <li class="lebensziel__phase">
                <h4>Phase {{$phase->lebenszielPhaseId->value}}</h4>
                <p>{{$phase->description}}</p>

                <div class="lebensziel__phase-kompetenzen">
                    <div>
                        <strong>Bildung & Karriere</strong>
                        <ul class="kompetenzen">
                            @for($i = 0; $i < $phase->bildungsKompetenzSlots; $i++)
                                <x-gameboard.kompetenzen.kompetenz-icon-bildung
                                     :draw-empty="true" />
                            @endfor
                        </ul>
                    </div>

                    <div>
                        <strong>Soziales & Freizeit</strong>
                        <ul class="kompetenzen">
                            @for($i = 0; $i < $phase->freizeitKompetenzSlots; $i++)
                                <x-gameboard.kompetenzen.kompetenz-icon-freizeit
                                     :draw-empty="true" />
                            @endfor
                        </ul>
                    </div>
                </div>

                @if($phase->investitionen > 0)
                <div>
                    <strong>Investitionen: </strong> {{$phase->investitionen}} €
                </div>
                @endif
            </li>
        @endforeach
    </ul>

</div>
