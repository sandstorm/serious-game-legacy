<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\Forms\PreGameNameLebenszielForm;
use Domain\CoreGameLogic\Feature\Initialization\Command\SelectLebensziel;
use Domain\CoreGameLogic\Feature\Initialization\Command\SelectPlayerColor;
use Domain\CoreGameLogic\Feature\Initialization\Command\SetNameForPlayer;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartGame;
use Domain\CoreGameLogic\Feature\Initialization\Event\PlayerColorWasSelected;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\Feature\Initialization\ValueObject\PlayerColor;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
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
        $this->nameLebenszielForm->name = PreGameState::nameForPlayerOrNull($this->gameStream, $this->myself) ?? '';
        $this->nameLebenszielForm->lebensziel = PreGameState::lebenszielForPlayerOrNull($this->gameStream, $this->myself)->id->value ?? null;
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
        $this->coreGameLogic->handle($this->gameId, new SetNameForPlayer($this->myself, $this->nameLebenszielForm->name));
        if ($this->nameLebenszielForm->lebensziel !== null) {
            $this->coreGameLogic->handle($this->gameId, new SelectLebensziel($this->myself, LebenszielId::create($this->nameLebenszielForm->lebensziel)));
        }

        // color is part of the command, in case players can select their own color
        $this->coreGameLogic->handle($this->gameId, new SelectPlayerColor($this->myself, null));

        $this->broadcastNotify();
    }

    public function selectLebensZiel(int $lebensziel): void
    {
        $this->nameLebenszielForm->lebensziel = $lebensziel;
    }

    public function startGame(): void
    {
        $this->coreGameLogic->handle($this->gameId, StartGame::create());
        $this->coreGameLogic->handle($this->gameId, ChangeKonjunkturphase::create());
        $this->broadcastNotify();
    }
}
