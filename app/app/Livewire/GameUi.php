<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Events\GameStateUpdated;
use App\Livewire\Forms\PreGameNameLebensziel;
use Domain\CoreGameLogic\DrivingPorts\ForCoreGameLogic;
use Domain\CoreGameLogic\Dto\ValueObject\GameId;
use Domain\CoreGameLogic\Dto\ValueObject\LebenszielId;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Command\SelectLebensziel;
use Domain\CoreGameLogic\Feature\Initialization\Command\SetNameForPlayer;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartGame;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SpielzugAbschliessen;
use Domain\CoreGameLogic\Feature\Spielzug\State\CurrentPlayerAccessor;
use Domain\Definitions\Lebensziel\LebenszielFinder;
use Illuminate\Events\Dispatcher;
use Livewire\Component;

class GameUi extends Component
{
    // Not the current player, but the player connected to THIS SESSION
    public PlayerId $myself;
    public GameId $gameId;
    private Dispatcher $eventDispatcher;
    private ForCoreGameLogic $coreGameLogic;
    private GameEvents $gameStream;

    public PreGameNameLebensziel $nameLebenszielForm;

    public function mount(): void
    {
        /*$this->name = Auth::user()->name;

        $this->email = Auth::user()->email;*/

        $this->nameLebenszielForm->name = PreGameState::nameForPlayerOrNull($this->gameStream, $this->myself) ?? '';
        $this->nameLebenszielForm->lebensziel = PreGameState::lebenszielForPlayerOrNull($this->gameStream, $this->myself)->id ?? null;
    }

    public function boot(Dispatcher $eventDispatcher, ForCoreGameLogic $coreGameLogic): void
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->coreGameLogic = $coreGameLogic;
        $this->gameStream = $this->coreGameLogic->getGameStream($this->gameId);
    }

    /**
     * @return string[]
     */
    public function getListeners(): array
    {
        return [
            "echo:game.{$this->gameId->value},GameStateUpdated" => 'notifyGameStateUpdated',
        ];
    }

    public function render(): \Illuminate\View\View
    {
        $lebensziele = LebenszielFinder::getAllLebensziele();

        return view('livewire.game-ui', [
            'lebensziele' => $lebensziele
        ]);
    }

    public function startGame(): void
    {
        $this->coreGameLogic->handle($this->gameId, new StartGame(
            playerOrdering: PreGameState::playerIds($this->gameStream),
        ));
        $this->broadcastNotify();
    }

    public function preGameSetNameAndLebensziel(): void
    {
        $this->nameLebenszielForm->validate();
        $this->coreGameLogic->handle($this->gameId, new SetNameForPlayer($this->myself, $this->nameLebenszielForm->name));
        if ($this->nameLebenszielForm->lebensziel !== null) {
            $this->coreGameLogic->handle($this->gameId, new SelectLebensziel($this->myself, new LebenszielId($this->nameLebenszielForm->lebensziel)));
        }
        $this->broadcastNotify();
    }

    public function selectLebensZiel(int $lebensziel): void
    {
        $this->nameLebenszielForm->lebensziel = $lebensziel;
    }

    public function gameStream(): GameEvents
    {
        return $this->gameStream;
    }

    public function currentPlayer(): PlayerId
    {
        return CurrentPlayerAccessor::forStream($this->gameStream);
    }

    public function spielzugAbschliessen(): void
    {
        $this->coreGameLogic->handle($this->gameId, new SpielzugAbschliessen($this->myself));

        $this->broadcastNotify();
    }

    public function notifyGameStateUpdated(): void
    {
        // the component automatically recalculates; so we do not need to do anything.
    }

    /**
     * Notify all connected clients that a Spielzug has happened
     */
    private function broadcastNotify(): void
    {
        $this->eventDispatcher->dispatch(new GameStateUpdated($this->gameId));
    }
}
