<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Events\GameStateUpdated;
use App\Livewire\Traits\CardTrait;
use App\Livewire\Traits\GameTrait;
use App\Livewire\Traits\KonjunkturzyklusTrait;
use App\Livewire\Traits\PlayerDetailsModalTrait;
use App\Livewire\Traits\PreGameTrait;
use Domain\CoreGameLogic\DrivingPorts\ForCoreGameLogic;
use Domain\CoreGameLogic\Dto\ValueObject\GameId;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\Feature\Spielzug\State\CurrentPlayerAccessor;
use Illuminate\Events\Dispatcher;
use Illuminate\View\View;
use Livewire\Component;

class GameUi extends Component
{
    use PlayerDetailsModalTrait;
    use CardTrait;
    use KonjunkturzyklusTrait;
    use PreGameTrait;
    use GameTrait;

    // injected from outside -> game-play.blade.php
    // Not the current player, but the player connected to THIS SESSION
    public PlayerId $myself;
    public GameId $gameId;

    private Dispatcher $eventDispatcher;
    private ForCoreGameLogic $coreGameLogic;
    private GameEvents $gameStream;

    public function mount(): void
    {
        $this->mountPreGame();
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

    public function render(): View
    {
        if (PreGameState::isInPreGamePhase($this->gameStream)) {
            return $this->renderPreGame();
        } else {
            return $this->renderGame();
        }
    }

    /**
     * @return GameEvents
     */
    public function gameStream(): GameEvents
    {
        return $this->gameStream;
    }

    /**
     * @return PlayerId
     */
    public function getCurrentPlayer(): PlayerId
    {
        return CurrentPlayerAccessor::forStream($this->gameStream);
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
