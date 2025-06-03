<div class="card-pile">
    <h3>{{ $title }}</h3>
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
                        @if ($card->resourceChanges->newErwerbseinkommen)
                            <li>Erwerbseinkommen: {{ $card->resourceChanges->newErwerbseinkommen}}€</li>
                        @endif
                        @if ($card->resourceChanges->erwerbseinkommenChangeInPercent)
                            <li>Erwebseinkommen %: {{ $card->resourceChanges->erwerbseinkommenChangeInPercent}}%</li>
                        @endif
                    </ul>
                </div>

                @if ($this->cardActionsVisible($card->id->value))
                    <div class="card-pile__card-actions">
                        <button type="button" class="button button--type-outline-primary"
                                wire:click="skipCard('{{$card->id->value}}', '{{$card->pileId->value}}')" @disabled(!$this->canSkipCard())
                        >
                            Karte skippen
                        </button>
                        <button type="button" class="button button--type-primary"
                                wire:click="activateCard('{{$card->id->value}}', '{{$card->pileId->value}}')" @disabled(!$this->canActivateCard($card->id->value))
                        >
                            Karte spielen
                        </button>
                    </div>
                @endif
            </li>
        @endif
    </ul>
</div>
