<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\InvestmentAmountChanges;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\LogEntry;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\Loggable;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesInvestmentAmountChanges;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ZeitsteinAktion;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Investments\ValueObject\InvestmentId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

class PlayerHasBoughtInvestment implements GameEventInterface, ProvidesResourceChanges, ZeitsteinAktion, ProvidesInvestmentAmountChanges, Loggable
{
    /**
     * @param PlayerId $playerId
     * @param InvestmentId $investmentId
     * @param MoneyAmount $price
     * @param int $amount
     * @param ResourceChanges $resourceChanges
     */
    public function __construct(
        public PlayerId        $playerId,
        protected InvestmentId $investmentId,
        public MoneyAmount     $price,
        public int             $amount,
        public ResourceChanges $resourceChanges,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['player']),
            investmentId: InvestmentId::from($values['investmentId']),
            price: new MoneyAmount($values['price']),
            amount: $values['amount'],
            resourceChanges: ResourceChanges::fromArray($values['resourceChanges']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'player' => $this->playerId,
            'investmentId' => $this->investmentId->value,
            'price' => $this->price->value,
            'amount' => $this->amount,
            'resourceChanges' => $this->resourceChanges,
        ];
    }

    public function getResourceChanges(PlayerId $playerId): ResourceChanges
    {
        if ($this->playerId->equals($playerId)) {
            return $this->resourceChanges;
        }
        return new ResourceChanges();
    }

    public function getCategoryId(): CategoryId
    {
        return CategoryId::INVESTITIONEN;
    }

    public function getPlayerId(): PlayerId
    {
        return $this->playerId;
    }

    public function getNumberOfZeitsteinslotsUsed(): int
    {
        return 1; // Buying investments uses one Zeitsteinslot
    }

    public function getInvestmentAmountChanges(PlayerId $playerId, InvestmentId $investmentId): InvestmentAmountChanges
    {
        if ($this->playerId->equals($playerId) && $this->investmentId === $investmentId) {
            return new InvestmentAmountChanges(
                amountChange: $this->amount
            );
        }
        return new InvestmentAmountChanges();
    }

    public function getLogEntry(): LogEntry
    {
        return new LogEntry(
            text: "Investiert in '" . $this->investmentId->value . "' und kauft " . $this->amount . " Anteile zum Preis von " . $this->price->formatWithoutHtml(),
            playerId: $this->playerId,
            resourceChanges: $this->resourceChanges,
        );
    }

    public function getInvestmentId(): InvestmentId
    {
        return $this->investmentId;
    }
}
