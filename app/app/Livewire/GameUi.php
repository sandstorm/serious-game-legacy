<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Events\GameStateUpdated;
use App\Livewire\Traits\HasBanner;
use App\Livewire\Traits\HasCard;
use App\Livewire\Traits\HasWeiterbildung;
use App\Livewire\Traits\HasGamePhase;
use App\Livewire\Traits\HasInvestitionen;
use App\Livewire\Traits\HasJobOffer;
use App\Livewire\Traits\HasKonjunkturphase;
use App\Livewire\Traits\HasLebenszielphase;
use App\Livewire\Traits\HasLog;
use App\Livewire\Traits\HasNotification;
use App\Livewire\Traits\HasPlayerDetails;
use App\Livewire\Traits\HasPreGamePhase;
use App\Livewire\Traits\HasMoneySheet;
use App\Livewire\Traits\HasMinijob;
use App\Livewire\Traits\HasQuitJob;
use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\DrivingPorts\ForCoreGameLogic;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Spielzug\State\CurrentPlayerAccessor;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\GameId;
use Domain\CoreGameLogic\PlayerId;
use Illuminate\Events\Dispatcher;
use Illuminate\View\View;
use Livewire\Component;

class GameUi extends Component
{
    use HasPlayerDetails;
    use HasCard;
    use HasKonjunkturphase;
    use HasPreGamePhase;
    use HasGamePhase;
    use HasMoneySheet;
    use HasJobOffer;
    use HasNotification;
    use HasLog;
    use HasMinijob;
    use HasLebenszielphase;
    use HasInvestitionen;
    use HasQuitJob;
    use HasWeiterbildung;
    use HasBanner;

    // injected from outside -> game-play.blade.php
    // Not the current player, but the player connected to THIS SESSION
    public PlayerId $myself;
    public GameId $gameId;

    private Dispatcher $eventDispatcher;
    private GameEvents $gameEvents;
    private ForCoreGameLogic $coreGameLogic;

    public function boot(Dispatcher $eventDispatcher, ForCoreGameLogic $coreGameLogic): void
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->coreGameLogic = $coreGameLogic;
        $this->gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
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
        if (PreGameState::isInPreGamePhase($this->gameEvents)) {
            return $this->renderPreGamePhase();
        }
        if (KonjunkturphaseState::hasCurrentKonjunkturphaseEnded($this->gameEvents)) {
            return $this->renderKonjunkturphaseEndScreen();
        }
        if (!KonjunkturphaseState::hasPlayerStartedCurrentKonjunkturphase($this->gameEvents, $this->myself)) {
            return $this->renderKonjunkturphaseStartScreen();
        }

        return $this->renderGamePhase();
    }

    /**
     * @return GameEvents
     */
    public function getGameEvents(): GameEvents
    {
        return $this->gameEvents;
    }

    /**
     * Always use this method to execute commands in the frontend, because it will
     * also update the local gameEvents.
     */
    public function handleCommand(CommandInterface $command): void
    {
        $this->coreGameLogic->handle($this->gameId, $command);
        $this->gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
    }

    /**
     * @return PlayerId
     */
    public function getCurrentPlayer(): PlayerId
    {
        return CurrentPlayerAccessor::forStream($this->gameEvents);
    }

    /**
     * @return bool
     */
    public function currentPlayerIsMyself(): bool
    {
        return $this->getCurrentPlayer() === $this->myself;
    }

    /**
     * @param PlayerId $playerId
     * @return bool
     */
    public function playerIsMyself(PlayerId $playerId): bool
    {
        return $this->myself === $playerId;
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

    public function getPlayerColorClass(): string
    {
        if (PreGameState::isInPreGamePhase($this->gameEvents)) {
            return "";
        }

        return PlayerState::getPlayerColorClass($this->gameEvents, $this->myself);
    }

    public function getPlayerPhase(?PlayerId $playerId = null): int
    {
        return PlayerState::getCurrentLebenszielphaseIdForPlayer($this->gameEvents, $playerId ?? $this->myself)->value;
    }

    public function getCurrentPlayerName(): string
    {
        return PlayerState::getNameForPlayer($this->gameEvents, $this->getCurrentPlayer());
    }

    public function getLebenszielNameForCurrentPlayer(): string
    {
        return PlayerState::getLebenszielDefinitionForPlayer($this->gameEvents, $this->getCurrentPlayer())->name;
    }
}
