@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')
@use('\App\Livewire\ValueObject\ExpensesTabEnum')

@props(['playerId' => null])

<div class="sidebar">
    <div class="sidebar__header">
        <div class="sidebar__lebensziel">
            <strong>Lebensziel:</strong>
            {{ PlayerState::getLebenszielDefinitionForPlayer($this->gameEvents, $playerId)->name }} <i class="icon-info" aria-hidden="true"></i>
        </div>
        <div class="sidebar__menu">
            <button class="button button--type-primary button--type-icon">
                <i class="icon-burger" aria-hidden="true"></i>
                <span class="sr-only">Menü öffnen</span>
            </button>
        </div>
    </div>

    <div class="sidebar__protocol">
        <strong>Ereignisprotokoll:</strong>
    </div>

    @if ($this->currentPlayerIsMyself())
        <div class="sidebar__actions">
            @if (PlayerState::getJobForPlayer($this->gameEvents, $playerId) !== null)
                <button class="button button--type-primary" wire:click="showIncomeTab('salary')">
                    Mein Job. {!! PlayerState::getCurrentGehaltForPlayer($this->gameEvents, $playerId)->format() !!}
                </button>
            @endif

            <button class="button button--type-primary" wire:click="showTakeOutALoan()">
                Kredit aufnehmen <i class="icon-dots" aria-hidden="true"></i>
            </button>
            <button class="button button--type-primary" wire:click="showExpensesTab('{{ ExpensesTabEnum::INSURANCES }}')">
                Versicherung abschließen <i class="icon-dots" aria-hidden="true"></i>
            </button>
            <button
                type="button"
                @class([
                    "button",
                    "button--type-primary",
                    "button--disabled" => !$this->canEndSpielzug()->canExecute,
                    $this->getButtonPlayerClass()
                ])
                wire:click="spielzugAbschliessen()">
                Spielzug beenden
            </button>
        </div>
    @endif
</div>

