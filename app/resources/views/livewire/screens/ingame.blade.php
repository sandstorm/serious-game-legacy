@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')

{{-- !!! Livewire components MUST have a single root element !!! --}}
<div class="game">
    <header class="game__header">
        <x-gameboard.player-list :myself="$myself" />
        @if ($showDetailsForPlayer)
            <x-player-details :player-id="$showDetailsForPlayer" :game-stream="$this->gameStream" />
        @endif
    </header>

    <div class="game__content">
        <div class="game-board">
            <div class="game-board__konjukturphase">
                Jahr: {{ $currentYear->value }} - {{ $konjunkturphasenDefinition->type }}
                <button type="button" class="button button--type-primary button--size-small" wire:click="showKonjunkturphaseDetails()">Zeige Details</button>
                @if ($konjunkturphaseDetailsVisible)
                    <x-konjunkturphase-detais :game-stream="$this->gameStream" />
                @endif
            </div>

            <div class="game-board__categories">
                @foreach($categories as $category)
                    <div class="game-board__category">
                        <h3>{{ $category['title'] }}</h3>
                        <ul class="zeitsteine">
                            @foreach($category['placedZeitsteine'] as $placedZeitstein)
                                @for($i = 0; $i < $placedZeitstein['zeitsteine']; $i++)
                                    <li class="zeitstein" @style(['background-color:' . PlayerState::getPlayerColor($this->gameStream(), $placedZeitstein['playerId'])])></li>
                                @endfor
                            @endforeach
                            @for($i = 0; $i < $category['availableZeitsteine']; $i++)
                                <li class="zeitstein zeitstein--empty"></li>
                            @endfor
                        </ul>

                        <ul class="kompetenzen">
                            @for($i = 0; $i < $category['kompetenzen']; $i++)
                                <li class="kompetenz"></li>
                            @endfor

                            @for($i = 0; $i < $category['kompetenzenRequiredByPhase']; $i++)
                                <li class="kompetenz kompetenz--empty"></li>
                            @endfor
                        </ul>
                        @if ($category['cardPile'] !== null)
                            <x-card-pile :category="$category['title']" :card-pile="$category['cardPile']" :game-stream="$this->gameStream" />
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <aside class="game__aside">
        <h4>Money Sheet</h4>
        <button class="button button--type-primary" wire:click="showMoneySheet()">{{ PlayerState::getGuthabenForPlayer($this->gameStream(), $myself) }}€</button>
        @if ($moneySheetIsVisible)
            <x-money-sheet :player-id="$myself" :game-stream="$this->gameStream"/>
        @endif

        @if ($this->currentPlayerIsMyself())
            <button type="button" class="button button--type-primary" wire:click="spielzugAbschliessen()">Spielzug abschließen</button>
        @endif
    </aside>
</div>
