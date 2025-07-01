@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')
@use('\App\Livewire\ValueObject\ExpensesTabEnum')
@use('\App\Livewire\ValueObject\IncomeTabEnum')

@extends ('components.modal.modal', ['closeModal' => "closeMoneySheet()", 'size' => 'medium'])
@section('title')
    Money Sheet
@endsection

@section('content')
    <x-gameboard.moneySheet.money-sheet :money-sheet="$moneySheet"/>
@endsection

@section('footer')
    <button
            type="button"
            @class([
                "button",
                "button--type-primary",
                "button--disabled" => !$this->canCompleteMoneysheet(),
            ])
            wire:click="completeMoneysheetForPlayer()"
    >
        Weiter brot
    </button>
@endsection
