@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')

{{-- !!! Livewire components MUST have a single root element !!! --}}
<div class="game">
    <header class="game__header">
        <x-gameboard.player-list :myself="$myself"/>
        @if ($showDetailsForPlayer)
            <x-player-details :player-id="$showDetailsForPlayer" :game-events="$this->gameEvents"/>
        @endif
    </header>

    <div class="game__content">
        <div class="game-board">
            <div class="game-board__konjukturphase">
                Jahr: {{ $currentYear->value }} - {{ $konjunkturphasenDefinition->type }}
                <button type="button" class="button button--type-primary button--size-small"
                        wire:click="showKonjunkturphaseDetails()">Zeige Details
                </button>
                @if ($konjunkturphaseDetailsVisible)
                    <x-konjunkturphase-details :game-events="$this->gameEvents"/>
                @endif
            </div>

            <div class="game-board__categories">
                @foreach($categories as $category)
                    <div class="game-board__category">
                        <h3>{{ $category->title }}</h3>
                        <ul class="zeitsteine">
                            @foreach($category->placedZeitsteine as $placedZeitstein)
                                @for($i = 0; $i < $placedZeitstein->zeitsteine; $i++)
                                    <li class="zeitsteine__item" @style(['background-color:' . PlayerState::getPlayerColor($this->gameEvents(), $placedZeitstein->playerId)])></li>
                                @endfor
                            @endforeach
                            @for($i = 0; $i < $category->availableZeitsteine; $i++)
                                <li class="zeitsteine__item zeitsteine__item--empty"></li>
                            @endfor
                        </ul>

                        <ul class="kompetenzen">
                            @for($i = 0; $i < $category->kompetenzen; $i++)
                                <li class="kompetenz"></li>
                            @endfor

                            @for($i = 0; $i < $category->kompetenzenRequiredByPhase; $i++)
                                <li class="kompetenz kompetenz--empty"></li>
                            @endfor
                        </ul>
                        @if ($category->cardPile !== null)
                            <x-card-pile :category="$category->title->value" :card-pile="$category->cardPile->value"
                                         :game-events="$this->gameEvents"/>
                        @endif

                        @if ($category->title->value === 'Erwerbseinkommen')
                            <button
                                type="button"
                                @class([
                                    "button",
                                    "button--type-primary",
                                    "button--disabled" => !$this->canRequestJobOffers()->canExecute,
                                ])
                                wire:click="showJobOffer()">
                                Jobangebote anschauen (-1 Zeitstein)
                            </button>

                            @if ($jobDefinition !== null)
                                <hr/>
                                <button class="button button--type-outline-primary" wire:click="showSalaryTab()">
                                    <ul class="zeitsteine">
                                        <li>-{{ $jobDefinition->requirements->zeitsteine }}</li>
                                        <li class="zeitsteine__item" @style(['background-color:' . PlayerState::getPlayerColor($this->gameEvents, $myself)])></li>
                                    </ul>
                                    <span>Mein Job. {{ $jobDefinition->gehalt->value}}€</span>
                                </button>
                            @endif
                            @if ($jobOfferIsVisible)
                                <x-job-offers-modal :player-id="$myself" :game-events="$this->gameEvents"/>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <aside class="game__aside">
        <h4>Money Sheet</h4>
        <button class="button button--type-primary"
                wire:click="showMoneySheet()">{{ PlayerState::getGuthabenForPlayer($this->gameEvents(), $myself) }}€
        </button>
        @if ($moneySheetIsVisible)
            @if ($editIncomeIsVisible)
                <x-gameboard.moneySheet.money-sheet-income-modal :player-id="$myself" :game-events="$this->gameEvents"/>
            @elseif ($editExpensesIsVisible)
                <x-gameboard.moneySheet.money-sheet-expenses-modal :player-id="$myself"
                                                                   :game-events="$this->gameEvents"/>
            @else
                <x-money-sheet.money-sheet :player-id="$myself" :game-events="$this->gameEvents"/>
            @endif
        @endif

        @if ($this->currentPlayerIsMyself())
            <hr/>
            <button
                type="button"
                @class([
                    "button",
                    "button--type-primary",
                    "button--disabled" => !$this->canEndSpielzug()->canExecute,
                ])
                wire:click="spielzugAbschliessen()">
                Spielzug abschließen
            </button>
        @endif
    </aside>
    <x-notification.notification/>

    <div class="dev-bar">
        <button type="button" class="button button--type-primary" wire:click="showLog()">Log</button>
        @if ($isLogVisible)
            <x-gameboard.log />
        @endif
    </div>
</div>
