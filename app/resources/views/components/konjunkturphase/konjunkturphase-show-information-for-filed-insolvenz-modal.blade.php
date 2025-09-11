@extends ('components.modal.modal', ['type' => "borderless"])

@section('icon')
    <i class="icon-phasenwechsel" aria-hidden="true"></i> Insolvenz angemeldet
@endsection

@section('content')
    <h1>Privatinsolvenz</h1>
    <p>Das Insolvenzverfahren streckt sich über 3 Jahren. Während dieser Zeit gelten folgende Konditionen: </p>
    <ul>
        <li><strong>Job:</strong> Du kannst Jobs aufnehmen/kündigen, jedoch nur maximal 10.000 Euro netto selbst behalten. Der restliche Betrag geht an die Insolvenzverwaltung.</li>
        <li><strong>Erbe/Gewinn:</strong> 50 % gehen an Insolvenzverwaltung.</li>
        <li><strong>Versicherung:</strong> Verboten abzuschließen.</li>
        <li><strong>Kredite:</strong> Verboten abzuschließen.</li>
        <li><strong>Lebenshaltungskosten:</strong> Müssen selbst getragen werden, wenn möglich.</li>
        <li><strong>Investitionen:</strong> Du darfst nicht investieren.</li>
    </ul>
    <p>Nach den drei Jahren erfolgt die Restschuldbefreiung – alle Schulden sind weg.</p>
@endsection

@section('footer')
    <button
        wire:click="toggleShowInformationForFiledInsolvenzModal()"
        type="button"
        @class([
            "button",
            "button--type-primary",
            $this->getPlayerColorClass(),
        ])
    >
        zurück
    </button>
@endsection
