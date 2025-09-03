@props([
    'bildungsKompetenzen' => null,
    'freizeitKompetenzen' => null
])

<span class="sr-only">{{ $bildungsKompetenzen->ariaLabel }}</span>
<ul class="kompetenzen">
    @foreach($bildungsKompetenzen->kompetenzSteine as $kompetenzStein)
        <x-dynamic-component :component="$kompetenzStein->iconComponentName"
             :player-name="$kompetenzStein->playerName"
             :player-color-class="$kompetenzStein->colorClass"
             :draw-empty="$kompetenzStein->drawEmpty"
             :draw-half-empty="$kompetenzStein->drawHalfEmpty"
        />
    @endforeach
</ul>

<span class="sr-only">{{ $freizeitKompetenzen->ariaLabel }}</span>
<ul class="kompetenzen">
    @foreach($freizeitKompetenzen->kompetenzSteine as $kompetenzStein)
        <x-dynamic-component :component="$kompetenzStein->iconComponentName"
            :player-name="$kompetenzStein->playerName"
            :player-color-class="$kompetenzStein->colorClass"
            :draw-empty="$kompetenzStein->drawEmpty"
            :draw-half-empty="$kompetenzStein->drawHalfEmpty"
        />
    @endforeach
</ul>
