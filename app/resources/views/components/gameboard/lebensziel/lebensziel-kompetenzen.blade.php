@props([
    'bildungsKompetenzen' => null,
    'freizeitKompetenzen' => null
])

<ul class="kompetenzen">
    <span class="sr-only">{{ $bildungsKompetenzen->ariaLabel }}</span>
    @foreach($bildungsKompetenzen->kompetenzen as $kompetenz)
        <x-dynamic-component :component="$kompetenz->iconComponentName"
             :player-name="$kompetenz->playerName"
             :player-color-class="$kompetenz->colorClass"
             :draw-empty="$kompetenz->drawEmpty"
             :draw-half-empty="$kompetenz->drawHalfEmpty"
        />
    @endforeach
</ul>

<ul class="kompetenzen">
    <span class="sr-only">{{ $freizeitKompetenzen->ariaLabel }}</span>
    @foreach($freizeitKompetenzen->kompetenzen as $kompetenz)
        <x-dynamic-component :component="$kompetenz->iconComponentName"
            :player-name="$kompetenz->playerName"
            :player-color-class="$kompetenz->colorClass"
            :draw-empty="$kompetenz->drawEmpty"
            :draw-half-empty="$kompetenz->drawHalfEmpty"
        />
    @endforeach
</ul>
