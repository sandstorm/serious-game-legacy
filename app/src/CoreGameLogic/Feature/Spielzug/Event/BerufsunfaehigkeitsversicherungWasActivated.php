<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\LogEntry;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\Loggable;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\HookEnum;
use Domain\Definitions\Card\ValueObject\ModifierId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;

/**
 * When the player loses their job due to an EreignisCard, which is insurable AND the player has a Berufsunfähigkeitsversicherung
 * at the time, then they will get one final payment at the end of the current Konjunkturphase.
 * They __should__ not be able to take a new job, because the will also get a Jobsperre until the end of the current
 * Konjunkturphase.
 * The Gehalt will be the modified Gehalt (with all active modifiers form EreignisCards).
 */
final readonly class BerufsunfaehigkeitsversicherungWasActivated implements GameEventInterface, Loggable
{
    public function __construct(
        public PlayerId $playerId,
        public Year $year,
        public MoneyAmount $gehalt,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            year: new Year($values['year']),
            gehalt: new MoneyAmount($values['gehalt']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId,
            'gehalt' => $this->gehalt,
            'year' => $this->year,
        ];
    }

    public function getLogEntry(): LogEntry
    {
        return new LogEntry(
            playerId: $this->playerId,
            text: "bekommt zum Jahresende " . $this->gehalt->value . " € von der Berufsunfähigkeitsversicherung"
        );
    }
}
