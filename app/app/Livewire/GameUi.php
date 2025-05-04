<?php

namespace App\Livewire;

use App\Events\GameStateUpdated;
use Domain\CoreGameLogic\DrivingPorts\ForCoreGameLogic;
use Domain\CoreGameLogic\Dto\ValueObject\GameId;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SpielzugAbschliessen;
use Domain\CoreGameLogic\GameState\CurrentPlayerAccessor;
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

    public function mount()
    {
        /*$this->name = Auth::user()->name;

        $this->email = Auth::user()->email;*/
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

    public function render()
    {
        return view('livewire.game-ui');
    }

    public function spielzugAbschliessen()
    {
        $this->coreGameLogic->handle($this->gameId, new SpielzugAbschliessen($this->myself));

        // Notify all connected clients that a Spielzug has happened
        $this->eventDispatcher->dispatch(new GameStateUpdated($this->gameId));
    }

    public function triggerGameAction(string $gameAction)
    {
        // TODO: IMPL ME
        $this->eventDispatcher->dispatch(new GameStateUpdated($this->gameId));
    }

    public function getListeners()
    {
        return [
            "echo:game.{$this->gameId->value},GameStateUpdated" => 'notifyGameStateUpdated',
        ];
    }

    public function notifyGameStateUpdated()
    {
        // the component automatically recalculates; so we do not need to do anything.
    }
}
