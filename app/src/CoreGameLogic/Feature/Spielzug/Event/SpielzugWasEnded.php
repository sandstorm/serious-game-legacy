<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\InvestmentPrice;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior\ProvidesInvestmentPriceChanges;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\LogEntry;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\Loggable;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Investments\ValueObject\InvestmentId;

final readonly class SpielzugWasEnded implements GameEventInterface, Loggable, ProvidesInvestmentPriceChanges
{
    /**
     * @param InvestmentPrice[] $investmentPrices
     */
    public function __construct(
        public PlayerId      $playerId,
        public PlayerTurn    $playerTurn,
        public array         $investmentPrices,
        public ?InvestmentId $idOfUpdatedInvestmentOrNull = null,
    )
    {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            playerTurn: new PlayerTurn($values['playerTurn']),
            investmentPrices: array_map(
                static fn($investmentPrice) => InvestmentPrice::fromArray($investmentPrice),
                $values['investmentPrices']
            ),
            idOfUpdatedInvestmentOrNull: $values['idOfUpdatedInvestmentOrNull'] !== null ? InvestmentId::from($values['idOfUpdatedInvestmentOrNull']) : null,
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId,
            'playerTurn' => $this->playerTurn,
            'investmentPrices' => $this->investmentPrices,
            'idOfUpdatedInvestmentOrNull' => $this->idOfUpdatedInvestmentOrNull->value ?? null,
        ];
    }

    public function getLogEntry(): LogEntry
    {
        $text = $this->idOfUpdatedInvestmentOrNull !== null
            ? "beendet den Spielzug und der Kurs für {$this->idOfUpdatedInvestmentOrNull->value} hat sich geändert"
            : "beendet den Spielzug";

        return new LogEntry(
            playerId: $this->playerId,
            text: $text
        );
    }

    /**
     * @return InvestmentPrice[]
     */
    public function getInvestmentPrices(): array
    {
        return $this->investmentPrices;
    }
}
