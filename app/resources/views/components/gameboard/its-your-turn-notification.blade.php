@extends ('components.modal.mandatory-modal', ['size' => "small"])

@section('icon_mandatory')
    <i class="icon-info-2" aria-hidden="true"></i>
@endsection

@section('content_mandatory')
    <h3>Du bist am Zug!</h3>
@endsection

@section('footer_mandatory')
    <button type="button"
            @class([
                "button",
                "button--type-primary",
                $this->getPlayerColorClass()
            ])
            wire:click="startSpielzug()"
    >
        Ok
    </button>
@endsection
