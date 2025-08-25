@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')
@use('\App\Livewire\ValueObject\ExpensesTabEnum')

@props([
    'gameEvents' => [],
    'playerId' => null
])

<div class="sidebar" x-data="{ eventLogOpen: false }" :class="eventLogOpen ? 'sidebar--event-log-open' : ''">
    <div class="sidebar__header">
        <button class="sidebar__lebensziel button button--type-text" title="Lebensziel anzeigen" wire:click="showPlayerLebensziel('{{ $playerId }}')">
            <strong>Lebensziel:</strong>
            {{ PlayerState::getLebenszielDefinitionForPlayer($gameEvents, $playerId)->name }}
            <i class="icon-info" aria-hidden="true"></i>
        </button>
        <div class="sidebar__menu">
            <button class="button button--type-primary button--type-icon">
                <i class="icon-burger" aria-hidden="true"></i>
                <span class="sr-only">Menü öffnen</span>
            </button>
        </div>
    </div>

    <div class="sidebar__eventlog">
        <x-sidebar.event-log />
    </div>

    @if ($this->currentPlayerIsMyself())
        <div class="sidebar__actions" x-show="!eventLogOpen">
            @if (PlayerState::getJobForPlayer($this->gameEvents, $playerId) !== null)
                <button class="button button--type-primary" wire:click="showIncomeTab('salary')">
                    Mein Job. {!! PlayerState::getCurrentGehaltForPlayer($this->gameEvents, $playerId)->format() !!}
                </button>
            @endif

            <button class="button button--type-primary" wire:click="showTakeOutALoan()">
                <span>Kredit aufnehmen</span> <i class="icon-dots" aria-hidden="true"></i>
            </button>
            <button class="button button--type-primary" wire:click="showExpensesTab('{{ ExpensesTabEnum::INSURANCES }}')">
                <span>Versicherung abschließen</span> <i class="icon-dots" aria-hidden="true"></i>
            </button>
            <button
                type="button"
                @class([
                    "button",
                    "button--type-primary",
                    "button--disabled" => !$this->canEndSpielzug()->canExecute,
                    $this->getPlayerColorClass()
                ])
                wire:click="spielzugAbschliessen()">
                Spielzug beenden
            </button>
        </div>
    @endif
</div>

