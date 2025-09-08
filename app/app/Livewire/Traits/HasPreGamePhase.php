<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\Forms\PreGameNameLebenszielForm;
use Domain\CoreGameLogic\Feature\Initialization\Command\SelectLebensziel;
use Domain\CoreGameLogic\Feature\Initialization\Command\SetNameForPlayer;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartGame;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Lebensziel\LebenszielFinder;
use Domain\Definitions\Lebensziel\ValueObject\LebenszielId;
use Illuminate\View\View;

trait HasPreGamePhase
{
    public PreGameNameLebenszielForm $nameLebenszielForm;

    /**
     * Prefixed with "mount" to avoid conflicts with Livewire's mount method.
     * Is automatically called by Livewire.
     * See https://livewire.laravel.com/docs/lifecycle-hooks#using-hooks-inside-a-trait
     *
     * @return void
     */
    public function mountHasPreGamePhase(): void
    {
        $this->nameLebenszielForm->name = PlayerState::getNameForPlayerOrNull($this->getGameEvents(), $this->myself) ?? '';
        $this->nameLebenszielForm->lebensziel = PlayerState::getLebenszielDefinitionForPlayerOrNull($this->getGameEvents(), $this->myself)->id->value ?? null;
    }

    public function renderPreGamePhase(): View
    {
        $lebensziele = LebenszielFinder::getAllLebensziele();

        return view('livewire.screens.pregame', [
            'lebensziele' => $lebensziele,
        ]);
    }

    public function preGameSetNameAndLebensziel(): void
    {
        $this->nameLebenszielForm->validate();
        $this->handleCommand(new SetNameForPlayer($this->myself, $this->nameLebenszielForm->name));
        if ($this->nameLebenszielForm->lebensziel !== null) {
            $this->handleCommand(new SelectLebensziel($this->myself, LebenszielId::create($this->nameLebenszielForm->lebensziel)));
        }

        $this->broadcastNotify();
    }

    public function selectLebensZiel(int $lebensziel): void
    {
        $this->nameLebenszielForm->lebensziel = $lebensziel;
    }

    public function startGame(): void
    {
        $this->handleCommand(StartGame::create());
        $this->handleCommand(ChangeKonjunkturphase::create());
        $this->broadcastNotify();
    }
}
