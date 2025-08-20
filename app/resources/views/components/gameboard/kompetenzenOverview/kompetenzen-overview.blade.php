@use('Domain\Definitions\Konjunkturphase\ValueObject\CategoryId')
@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')
@use('Domain\CoreGameLogic\Feature\MoneySheet\State\MoneySheetState')

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
                <div class="kompetenzen-overview__money-sheet-button">
                    <button title="Moneysheet öffnen" @class([
                        'button',
                        'button--type-primary',
                        $this->getPlayerColorClass()
                    ]) wire:click="showMoneySheet()">
                        {!! PlayerState::getGuthabenForPlayer($gameEvents, $playerId)->format() !!}

                        @if(MoneySheetState::doesMoneySheetRequirePlayerAction($gameEvents, $playerId))
                            <div class="moneysheet__action-required"><span class="sr-only">Berechnung erforderlich</span></div>
                        @else
                            <i class="icon-pencil-2" aria-hidden="true"></i>
                        @endif
                    </button>
                </div>

                <div class="kompetenzen-overview__investitionen-target" title="Benötigte Investitionen für die nächste Phase">
                    <i class="icon-phasenwechsel" aria-hidden="true"></i> {!! $investitionen->format() !!}
                </div>
            @endif
        </div>
    @endforeach
</div>
