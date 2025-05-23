<div class="card-pile">
    <h3>{{ $title }}</h3>
    <ul class="card-pile__cards">
        @if ($card)
            <li class="card-pile__card @if ($this->cardActionsVisible($card->id->value)) card-pile__card--show-actions @endif"
                wire:click="showCardActions('{{$card->id->value}}')">
                <div class="card-pile__card-details">
                    <h4>#{{ $card->id->value }} - {{ $card->kurzversion }}</h4>
                    <p>
                        {{ $card->langversion }}
                    </p>

                    <h5>Voraussetzungen:</h5>
                    <ul>
                        @if ($card->additionalRequirements->guthaben)
                            <li>Guthaben: {{ $card->additionalRequirements->guthaben}}€</li>
                        @endif
                        @if ($card->additionalRequirements->zeitsteine)
                            <li>Zeitsteine: {{ $card->additionalRequirements->zeitsteine}}</li>
                        @endif
                        @if ($card->additionalRequirements->bildungKompetenzsteine)
                            <li>Bildung: {{ $card->additionalRequirements->bildungKompetenzsteine}}</li>
                        @endif
                        @if ($card->additionalRequirements->freizeitKompetenzsteine)
                            <li>Freizeit: {{ $card->additionalRequirements->freizeitKompetenzsteine}}</li>
                        @endif
                    </ul>

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
                                wire:click="skipCard('{{$card->id->value}}', '{{$card->pileId->value}}')"@if (!$this->canSkipCard()) disabled @endif
                        >
                            Karte skippen
                        </button>
                        <button type="button" class="button button--type-primary"
                                wire:click="activateCard('{{$card->id->value}}', '{{$card->pileId->value}}')"@if (!$this->canActivateCard($card->id->value)) disabled @endif
                        >
                            Karte spielen
                        </button>
                    </div>
                @endif
            </li>
        @endif
    </ul>
</div>
