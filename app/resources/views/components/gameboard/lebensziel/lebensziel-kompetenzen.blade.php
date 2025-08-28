@props([
    'bildungsKompetenzen' => null,
    'freizeitKompetenzen' => null
])

<ul class="kompetenzen">
    <span class="sr-only">{{ $bildungsKompetenzen->ariaLabel }}</span>
    @foreach($bildungsKompetenzen->kompetenzSteine as $kompetenzStein)
        <x-dynamic-component :component="$kompetenzStein->iconComponentName"
             :player-name="$kompetenzStein->playerName"
             :player-color-class="$kompetenzStein->colorClass"
             :draw-empty="$kompetenzStein->drawEmpty"
             :draw-half-empty="$kompetenzStein->drawHalfEmpty"
        />
    @endforeach
</ul>

<ul class="kompetenzen">
    <span class="sr-only">{{ $freizeitKompetenzen->ariaLabel }}</span>
    @foreach($freizeitKompetenzen->kompetenzSteine as $kompetenzStein)
        <x-dynamic-component :component="$kompetenzStein->iconComponentName"
            :player-name="$kompetenzStein->playerName"
            :player-color-class="$kompetenzStein->colorClass"
            :draw-empty="$kompetenzStein->drawEmpty"
            :draw-half-empty="$kompetenzStein->drawHalfEmpty"
        />
    @endforeach
</ul>
