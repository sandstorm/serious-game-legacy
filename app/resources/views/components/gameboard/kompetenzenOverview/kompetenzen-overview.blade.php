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

            @if ($category->kompetenzen !== null && count($category->kompetenzen->kompetenzSteine) > 0)
                <div class="kompetenzen">
                    <span class="sr-only">{{ $category->kompetenzen->ariaLabel }}</span>
                    @foreach($category->kompetenzen->kompetenzSteine as $kompetenzSteine)
                        <x-dynamic-component :component="$kompetenzSteine->iconComponentName"
                            :player-name="$kompetenzSteine->playerName"
                            :player-color-class="$kompetenzSteine->colorClass"
                            :draw-empty="$kompetenzSteine->drawEmpty"
                            :draw-half-empty="$kompetenzSteine->drawHalfEmpty"
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

                        <div @class([
                                'kompetenzen-overview__action-required',
                                MoneySheetState::doesMoneySheetRequirePlayerAction($gameEvents, $playerId) ? 'kompetenzen-overview__action-required--active' : ''
                            ])
                        >
                            <i class="icon-pencil" aria-hidden="true"></i>
                            @if (MoneySheetState::doesMoneySheetRequirePlayerAction($gameEvents, $playerId))
                                <span class="sr-only">Berechnung erforderlich</span>
                            @endif
                        </div>
                    </button>
                </div>

                <div class="kompetenzen-overview__investitionen-target"
                     title="Benötigte Investitionen für die nächste Phase">
                    <i class="icon-phasenwechsel" aria-hidden="true"></i> {!! $investitionen->format() !!}
                </div>
            @endif
        </div>
    @endforeach
</div>
