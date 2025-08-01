@props([
    'bildungsKompetenzen' => null,
    'freizeitKompetenzen' => null
])

<ul class="kompetenzen">
    @foreach($bildungsKompetenzen as $kompetenz)
        <x-dynamic-component :component="$kompetenz->iconComponentName"
             :player-name="$kompetenz->playerName"
             :player-color-class="$kompetenz->colorClass"
             :draw-empty="$kompetenz->drawEmpty"
        />
    @endforeach
</ul>

<ul class="kompetenzen">
    @foreach($freizeitKompetenzen as $kompetenz)
        <x-dynamic-component :component="$kompetenz->iconComponentName"
            :player-name="$kompetenz->playerName"
            :player-color-class="$kompetenz->colorClass"
            :draw-empty="$kompetenz->drawEmpty"
        />
    @endforeach
</ul>
