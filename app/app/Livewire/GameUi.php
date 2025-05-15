<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Events\GameStateUpdated;
use App\Livewire\Forms\PreGameNameLebensziel;
use Domain\CoreGameLogic\DrivingPorts\ForCoreGameLogic;
use Domain\CoreGameLogic\Dto\ValueObject\GameId;
use Domain\CoreGameLogic\Dto\ValueObject\Lebensziel;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Command\LebenszielAuswaehlen;
use Domain\CoreGameLogic\Feature\Initialization\Command\SetNameForPlayer;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartGame;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SpielzugAbschliessen;
use Domain\CoreGameLogic\Feature\Spielzug\State\CurrentPlayerAccessor;
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

    public function mount(): void
    {
        /*$this->name = Auth::user()->name;

        $this->email = Auth::user()->email;*/

        $this->nameLebenszielForm->name = PreGameState::nameForPlayerOrNull($this->gameStream, $this->myself) ?? '';
        $this->nameLebenszielForm->lebensziel = PreGameState::lebenszielForPlayerOrNull($this->gameStream, $this->myself)->value ?? '';
    }

    public function startGame(): void
    {
        $this->coreGameLogic->handle($this->gameId, new StartGame(
            playerOrdering: PreGameState::playerIds($this->gameStream),
        ));
        $this->broadcastNotify();
    }

    public PreGameNameLebensziel $nameLebenszielForm;

    public function preGameSetNameAndLebensziel(): void
    {
        $this->nameLebenszielForm->validate();
        $this->coreGameLogic->handle($this->gameId, new SetNameForPlayer($this->myself, $this->nameLebenszielForm->name));
        $this->coreGameLogic->handle($this->gameId, new LebenszielAuswaehlen($this->myself, new Lebensziel($this->nameLebenszielForm->lebensziel)));
        $this->broadcastNotify();
    }

    public function selectLebensZiel(String $lebensziel): void
    {
        $this->nameLebenszielForm->lebensziel = $lebensziel;
    }

    public function gameStream(): GameEvents
    {
        return $this->gameStream;
    }

    public function boot(Dispatcher $eventDispatcher, ForCoreGameLogic $coreGameLogic): void
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->coreGameLogic = $coreGameLogic;
        $this->gameStream = $this->coreGameLogic->getGameStream($this->gameId);
    }

    public function currentPlayer(): PlayerId
    {
        return CurrentPlayerAccessor::forStream($this->gameStream);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.game-ui');
    }

    public function spielzugAbschliessen(): void
    {
        $this->coreGameLogic->handle($this->gameId, new SpielzugAbschliessen($this->myself));

        $this->broadcastNotify();
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
