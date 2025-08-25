@extends ('components.modal.mandatory-modal', ['size' => "small"])

@props([
    'winnerName' => '',
    'lebenszielName' => '',
])

@section('icon_mandatory')
    <i class="icon-info" aria-hidden="true"></i>
@endsection

@section('content_mandatory')
    <h3>{{ $winnerName }} hat das Lebensziel '{{ $lebenszielName }}' erreicht!</h3>
@endsection

@section('footer_mandatory')
    <button type="button"
            @class([
                "button",
                "button--type-primary",
                $this->getPlayerColorClass()
            ])
            wire:click="endGame()"
    >
        Spiel beenden
    </button>
@endsection
