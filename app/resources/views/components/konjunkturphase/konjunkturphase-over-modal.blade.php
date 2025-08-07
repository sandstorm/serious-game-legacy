@extends ('components.modal.modal', ['type' => "borderless"])

@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState')
@use('\App\Livewire\ValueObject\ExpensesTabEnum')

@props([])

@section('content')
    <div class="konjunkturphase-over">
        <h1>
            Die Konjunkturphase "{{KonjunkturphaseState::getCurrentKonjunkturphase($this->gameEvents())->type->value}}" ist zu Ende.
        </h1>
    </div>
@endsection

@section('footer')
    <button
        wire:click="showExpensesTab('{{ ExpensesTabEnum::TAXES }}')"
        type="button"
        @class([
            "button",
            "button--type-primary",
            $this->getPlayerColorClass(),
        ])
    >
        Weiter
    </button>
@endsection
