@use('\Domain\Definitions\Konjunkturphase\ValueObject\CategoryId')
@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')

@props([
    'categories' => [],
])

<div class="kompetenzen-overview">
    @foreach($categories as $category)
        <div class="kompetenzen-overview__category">
            <h4>{{ $category->title }}</h4>

            @if (count($category->kompetenzen) > 0)
                <ul class="kompetenzen">
                    @foreach($category->kompetenzen as $kompetenz)
                        <x-dynamic-component :component="$kompetenz->iconComponentName"
                            :playerName="$kompetenz->playerName"
                            :playerColorClass="$kompetenz->colorClass"
                            :drawEmpty="$kompetenz->drawEmpty" />
                    @endforeach
                </ul>
            @endif

            @if ($category->title === CategoryId::INVESTITIONEN)
                <button @class([
                    'button',
                    'button--type-primary',
                    PlayerState::getPlayerColorClass($gameEvents, $playerId)
                ]) wire:click="showMoneySheet()">
                    {!! PlayerState::getGuthabenForPlayer($gameEvents, $playerId)->format() !!}
                </button>
                TODO
            @endif
        </div>
    @endforeach
</div>
