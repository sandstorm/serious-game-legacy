@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')
@use('\App\Livewire\ValueObject\ExpensesTabEnum')

@props([
    'gameEvents' => [],
    'playerId' => null
])

<div class="sidebar" x-data="{ eventLogOpen: false }" :class="eventLogOpen ? 'sidebar--event-log-open' : ''">
    <div class="sidebar__header">
        <button class="sidebar__lebensziel button button--type-text" title="Lebensziel anzeigen" wire:click="showPlayerLebensziel('{{ $playerId }}')">
            <strong>Dein Lebensziel</strong>
            <i class="icon-info" aria-hidden="true"></i>
        </button>
        <div class="sidebar__menu">
            <button class="button button--type-secondary button--type-icon" wire:click="toggleSidebarMenu()">
                <i class="icon-burger" aria-hidden="true"></i>
                <span class="sr-only">Menü öffnen</span>
            </button>
        </div>
    </div>

    @if ($this->isSidebarMenuVisible)
        <x-sidebar.sidebar-menu-modal />
    @endif

    <div class="sidebar__eventlog">
        <x-sidebar.event-log />
    </div>

    @if ($this->currentPlayerIsMyself())
        <div class="sidebar__actions" x-show="!eventLogOpen">
            @if (PlayerState::getJobForPlayer($this->gameEvents, $playerId) !== null)
                <button class="button button--type-secondary" wire:click="showIncomeTab('salary')">
                    <span>Mein Job: {!! PlayerState::getCurrentGehaltForPlayer($this->gameEvents, $playerId)->format() !!}</span>
                </button>
            @endif

            <button
                @class([
                    "button",
                    "button--type-secondary",
                    $this->isPlayerAllowedToTakeOutALoan() ? "" : "button--disabled",
                ])
                wire:click="showTakeOutALoan()">
                    <span>Kredit aufnehmen</span> <i class="icon-dots" aria-hidden="true"></i>
            </button>
            <button class="button button--type-secondary" wire:click="showExpensesTab('{{ ExpensesTabEnum::INSURANCES }}')">
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

