@use('\Domain\Definitions\Konjunkturphase\ValueObject\CategoryId')
@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')

@props([
    'categories' => [],
    'investitionen' => null,
])

<div class="kompetenzen-overview">
    @foreach($categories as $category)
        <div class="kompetenzen-overview__category">
            <h4>{{ $category->title }}</h4>

            @if (count($category->kompetenzen) > 0)
                <div class="kompetenzen">
                    @foreach($category->kompetenzen as $kompetenz)
                        <x-dynamic-component :component="$kompetenz->iconComponentName"
                            :player-name="$kompetenz->playerName"
                            :player-color-class="$kompetenz->colorClass"
                            :draw-empty="$kompetenz->drawEmpty"
                            :draw-half-empty="$kompetenz->drawHalfEmpty"
                        />
                    @endforeach
                </div>
            @endif

            @if ($category->title === CategoryId::INVESTITIONEN)
                <button title="Moneysheet öffnen" @class([
                    'button',
                    'button--type-primary',
                    PlayerState::getPlayerColorClass($gameEvents, $playerId)
                ]) wire:click="showMoneySheet()">
                    {!! PlayerState::getGuthabenForPlayer($gameEvents, $playerId)->format() !!}
                </button>
                <div class="kompetenzen-overview__investitionen-target" title="Benötigte Investitionen für die nächste Phase">
                    {!! $investitionen->format() !!} <i class="icon-phasenwechsel" aria-hidden="true"></i>
                </div>
            @endif
        </div>
    @endforeach
</div>
