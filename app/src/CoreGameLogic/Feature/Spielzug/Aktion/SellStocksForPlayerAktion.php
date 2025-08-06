<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasAnotherPlayerBoughtStocksThisTurnValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerEnoughStocksToSellValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerInteractedWithStocksModalThisTurnValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\IsPlayersTurnValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\StocksWereSoldForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\StockType;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

class SellStocksForPlayerAktion extends Aktion
{
    private StockType $stockType;
    private MoneyAmount $sharePrice;
    private int $amount;

    public function __construct(
        StockType   $stockType,
        MoneyAmount $sharePrice,
        int         $amount
    ) {
        parent::__construct('sell-stocks', 'Aktien verkaufen');

        $this->stockType = $stockType;
        $this->sharePrice = $sharePrice;
        $this->amount = $amount;
    }

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $validationChain = new HasAnotherPlayerBoughtStocksThisTurnValidator($this->stockType);
        $validationChain
            ->setNext(new HasPlayerEnoughStocksToSellValidator($this->stockType, $this->amount))
            ->setNext(new HasPlayerInteractedWithStocksModalThisTurnValidator());
        return $validationChain->validate($gameEvents, $playerId);
    }

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($playerId, $gameEvents);
        if (!$result->canExecute) {
            throw new \RuntimeException('' . $result->reason, 1752753850);
        }

        return GameEventsToPersist::with(
            new StocksWereSoldForPlayer(
                playerId: $playerId,
                stockType: $this->stockType,
                sharePrice: $this->sharePrice,
                amount: $this->amount,
            )
        );
    }
}
