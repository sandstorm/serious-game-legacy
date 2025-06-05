@props(['lebensziel' => null])

<div class="lebensziel">
    <header>
        Lebensziel: {{$lebensziel->name}}
    </header>

    <ul class="lebensziel__phasen">
        @foreach($lebensziel->phaseDefinitions as $phase)
            <li class="lebensziel__phase">
                <h4>Phase {{$phase->phase}}</h4>
                <p>{{$phase->description}}</p>

                <div class="lebensziel__phase-kompetenzen">
                    <div>
                        <strong>Bildung & Karriere</strong>
                        <div class="kompetenzen">
                            @for($i = 0; $i < $phase->bildungsKompetenzSlots; $i++)
                                <div class="kompetenz"></div>
                            @endfor
                        </div>
                    </div>

                    <div>
                        <strong>Soziales & Freizeit</strong>
                        <div class="kompetenzen">
                            @for($i = 0; $i < $phase->freizeitKompetenzSlots; $i++)
                                <div class="kompetenz"></div>
                            @endfor
                        </div>
                    </div>
                </div>

                @if($phase->invenstition > 0)
                <div>
                    <strong>Investitionen: </strong> {{$phase->invenstition}} €
                </div>
                @endif
                @if($phase->erwerbseinkommen > 0)
                    <div>
                        <strong>Erwerbseinkommen: </strong> {{$phase->erwerbseinkommen}} €
                    </div>
                @endif
            </li>
        @endforeach
    </ul>

</div>
