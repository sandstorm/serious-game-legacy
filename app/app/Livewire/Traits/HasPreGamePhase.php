<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\Forms\PreGameLebenszielForm;
use App\Livewire\Forms\PreGameNameForm;
use Domain\CoreGameLogic\Feature\Initialization\Command\SelectLebensziel;
use Domain\CoreGameLogic\Feature\Initialization\Command\SetNameForPlayer;
use Domain\CoreGameLogic\Feature\Initialization\Command\SetRoleForPlayer;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartGame;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Lebensziel\LebenszielFinder;
use Domain\Definitions\Lebensziel\ValueObject\LebenszielId;
use Domain\Definitions\PlayerRole\PlayerRole;
use Illuminate\View\View;

trait HasPreGamePhase
{
    public PreGameNameForm $nameForm;
    public PreGameLebenszielForm $lebenszielForm;

    /**
     * Prefixed with "mount" to avoid conflicts with Livewire's mount method.
     * Is automatically called by Livewire.
     * See https://livewire.laravel.com/docs/lifecycle-hooks#using-hooks-inside-a-trait
     *
     * @return void
     */
    public function mountHasPreGamePhase(): void
    {
        $this->nameForm->name = PlayerState::getNameForPlayerOrNull($this->getGameEvents(), $this->myself) ?? '';
        $this->nameForm->role = PlayerState::getRoleForPlayerOrNull($this->getGameEvents(), $this->myself)?->value;
        $this->lebenszielForm->lebensziel = PlayerState::getLebenszielDefinitionForPlayerOrNull($this->getGameEvents(), $this->myself)?->id->value;
    }

    public function renderPreGamePhase(): View
    {
        return view('livewire.screens.pregame', [
            'lebensziele' => LebenszielFinder::getAllLebensziele(),
        ]);
    }

    public function preGameSetName(): void
    {
        $this->nameForm->validate();

        if ($this->showRoleSelection) {
            $this->validate([
                'nameForm.role' => ['required'],
            ], [
                'nameForm.role.required' => 'Bitte wähle eine Rolle aus.',
            ]);
            $this->handleCommand(new SetNameForPlayer($this->myself, $this->nameForm->name));

            if ($this->nameForm->role === null) {
                throw new \InvalidArgumentException('Role must not be null');
            }
            $this->handleCommand(new SetRoleForPlayer($this->myself, PlayerRole::from($this->nameForm->role)));
        } else {
            $this->handleCommand(new SetNameForPlayer($this->myself, $this->nameForm->name));
        }

        $this->broadcastNotify();
    }

    public function preGameSetLebensziel(): void
    {
        $this->lebenszielForm->validate();

        // Extra safety check, should never happen due to validation
        if ($this->lebenszielForm->lebensziel === null) {
            throw new \InvalidArgumentException('Lebensziel must not be null');
        }

        $this->handleCommand(new SelectLebensziel($this->myself, LebenszielId::create($this->lebenszielForm->lebensziel)));
        $this->broadcastNotify();
    }

    public function selectLebensZiel(int $lebensziel): void
    {
        $this->lebenszielForm->lebensziel = $lebensziel;
    }

    public function startGame(): void
    {
        $this->handleCommand(StartGame::create());
        $this->handleCommand(ChangeKonjunkturphase::create());
        $this->broadcastNotify();
    }
}
