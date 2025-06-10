@extends ('components.modal.modal', ['closeModal' => "closeJobOffer()"])
@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')

@section('title')
    Jobangebote
@endsection

@section('content')
    <h2>Jobangebote</h2>
    <p>Hier kannst du dir die Jobangebote anschauen, die dir zur Verfügung stehen.</p>
    <div class="job-offers">
        @foreach($jobOffers as $jobOffer)
            <div class="job-offers__item">
                <h5>{{ $jobOffer->title }}</h5>
                {{ $jobOffer->description }}
                <hr />
                + {{ $jobOffer->gehalt->value }}€ p.a. <br />

                <div class="job-offers__item-cost">
                    <span>-</span>
                    <ul class="zeitsteine">
                        @for($i = 0; $i < $jobOffer->requirements->zeitsteine; $i++)
                            <li class="zeitsteine__item" @style(['background-color:' . PlayerState::getPlayerColor($gameStream, $playerId)])></li>
                        @endfor
                    </ul>
                </div>

                <hr />
                <button type="button" class="button button--type-primary" wire:click="applyForJob('{{ $jobOffer->id->value }}')">Das mache ich!</button>
            </div>
        @endforeach
    </div>
@endsection

@section('footer')
    <button type="button" class="button button--type-primary" wire:click="closeJobOffer()">Schließen</button>
@endsection
