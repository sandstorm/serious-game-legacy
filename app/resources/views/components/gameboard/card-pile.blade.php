<div class="card-pile">
    <h3>{{ $title }}</h3>
    <ul class="card-pile__cards">
        <li class="card-pile__card" wire:click="activateCard('{{$card->id->value}}')">
            <h4>#{{ $card->id->value }} - {{ $card->kurzversion }}</h4>
            <p>
                {{ $card->langversion }}
            </p>

            @if ($this->cardIsActive($card->id->value))
                <div class="card-pile__card-actions">
                    <button type="button" class="button button--type-outline-primary" wire:click="skipCard('{{$card->id->value}}')">Karte skippen</button>
                    <button type="button" class="button button--type-primary" wire:click="playCard('{{$card->id->value}}')">Karte spielen</button>
                </div>
            @else
                <h5>Voraussetzungen:</h5>
                <ul>
                    @if ($card->requirements->guthaben)<li>Guthaben: {{ $card->requirements->guthaben}}€</li>@endif
                    @if ($card->requirements->zeitsteine)<li>Zeitsteine: {{ $card->requirements->zeitsteine}}</li>@endif
                    @if ($card->requirements->bildungKompetenzsteine)<li>Bildung: {{ $card->requirements->bildungKompetenzsteine}}</li>@endif
                    @if ($card->requirements->freizeitKompetenzsteine)<li>Freizeit: {{ $card->requirements->freizeitKompetenzsteine}}</li>@endif
                </ul>

                <h5>Bringt dir:</h5>
                <ul>
                    @if ($card->resourceChanges->guthabenChange)<li>Guthaben: {{ $card->resourceChanges->guthabenChange}}€</li>@endif
                    @if ($card->resourceChanges->zeitsteineChange)<li>Zeitstein: {{ $card->resourceChanges->zeitsteineChange}}</li>@endif
                    @if ($card->resourceChanges->bildungKompetenzsteinChange)<li>Bildung: {{ $card->resourceChanges->bildungKompetenzsteinChange}}</li>@endif
                    @if ($card->resourceChanges->freizeitKompetenzsteinChange)<li>Freizeit: {{ $card->resourceChanges->freizeitKompetenzsteinChange}}</li>@endif
                    @if ($card->resourceChanges->newErwerbseinkommen)<li>Erwerbseinkommen: {{ $card->resourceChanges->newErwerbseinkommen}}€</li>@endif
                    @if ($card->resourceChanges->erwerbseinkommenChangeInPercent)<li>Erwebseinkommen %: {{ $card->resourceChanges->erwerbseinkommenChangeInPercent}}%</li>@endif
                </ul>
            @endif
        </li>
    </ul>
</div>
