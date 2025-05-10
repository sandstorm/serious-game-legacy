<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\State;

use Domain\CoreGameLogic\Dto\Aktion\Aktion;
use Domain\CoreGameLogic\Dto\Aktion\PhaseWechseln;
use Domain\CoreGameLogic\Dto\Aktion\ZeitsteinSetzen;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEvents;

final readonly class AktionsCalculator
{
    private function __construct(
        private GameEvents $stream,
    ) {
    }

    public static function forStream(GameEvents $stream): self
    {
        return new self($stream);
    }

    /**
     * @return Aktion[]
     */
    public function availableActionsForPlayer(PlayerId $player): array
    {
        $aktionen = self::standardAktionen();
        $modifiersForPlayer = ModifierCalculator::forStream($this->stream)->forPlayer($player);
        $applicableAktionen = array_values(array_filter($aktionen, fn (Aktion $aktion) => $aktion->canExecute($player, $this->stream)));
        return $modifiersForPlayer->applyToAvailableAktionen($applicableAktionen);
    }

    /**
     * @return Aktion[]
     */
    private static function standardAktionen(): array
    {
        return [
            new PhaseWechseln(),
            new ZeitsteinSetzen(),
        ];
    }
}
