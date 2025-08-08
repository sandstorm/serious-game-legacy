@extends ('components.modal.modal', ['type' => "mandatory"])

@section('icon')
    <i class="icon-info" aria-hidden="true"></i>
@endsection

@section('content')
    <h3>Du bist am Zug!</h3>
@endsection

@section('footer')
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
