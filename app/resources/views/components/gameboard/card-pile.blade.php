<div class="card-pile">
    <ul class="card-pile__cards">
        @if ($card)
            <li
                @class([
                    'card-pile__card',
                    'card-pile__card--show-actions' => $this->cardActionsVisible($card->id->value)
                ])
                wire:click="showCardActions('{{$card->id->value}}')">
                <div class="card-pile__card-details">
                    <h4>#{{ $card->id->value }} - {{ $card->title }}</h4>
                    <p>
                        {{ $card->description }}
                    </p>

                    <h5>Bringt dir:</h5>
                    <ul>
                        @if ($card->resourceChanges->guthabenChange)
                            <li>Guthaben: {{ $card->resourceChanges->guthabenChange}}€</li>
                        @endif
                        @if ($card->resourceChanges->zeitsteineChange)
                            <li>Zeitstein: {{ $card->resourceChanges->zeitsteineChange}}</li>
                        @endif
                        @if ($card->resourceChanges->bildungKompetenzsteinChange)
                            <li>Bildung: {{ $card->resourceChanges->bildungKompetenzsteinChange}}</li>
                        @endif
                        @if ($card->resourceChanges->freizeitKompetenzsteinChange)
                            <li>Freizeit: {{ $card->resourceChanges->freizeitKompetenzsteinChange}}</li>
                        @endif
                    </ul>
                </div>

                @if ($this->cardActionsVisible($card->id->value))
                    <div class="card-pile__card-actions">
                        <button
                            type="button"
                            @class([
                                "button",
                                "button--type-outline-primary",
                                "button--disabled" => !$this->canSkipCard($category)->canExecute,
                            ])
                            wire:click="skipCard('{{$category}}')"
                        >
                            Karte skippen
                        </button>
                        <button
                            type="button"
                            @class([
                               "button",
                               "button--type-primary",
                               "button--disabled" => !$this->canActivateCard($category)->canExecute,
                           ])
                            wire:click="activateCard('{{$category}}')"
                        >
                            Karte spielen
                        </button>
                    </div>
                @endif
            </li>
        @endif
    </ul>
</div>
