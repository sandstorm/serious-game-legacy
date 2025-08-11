<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\StockPriceState;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasCategoryFreeZeitsteinslotsValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerDoneNoZeitsteinaktionThisTurnValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerEnoughResourcesValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerEnoughZeitsteineValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\IsPlayerAllowedToInvestValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\IsPlayersTurnValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\StocksWereBoughtForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\StockType;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

class BuyStocksForPlayerAktion extends Aktion
{
    private StockType $stockType;
    private MoneyAmount $sharePrice;
    private int $amount;

    public function __construct(
        StockType   $stockType,
        MoneyAmount $sharePrice,
        int         $amount
    ) {
        parent::__construct('buy-stocks', 'Aktien kaufen');

        $this->stockType = $stockType;
        $this->sharePrice = $sharePrice;
        $this->amount = $amount;
    }

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $validationChain = new IsPlayersTurnValidator();
        $validationChain
            ->setNext(new IsPlayerAllowedToInvestValidator())
            ->setNext(new HasPlayerEnoughZeitsteineValidator(1))
            ->setNext(new HasPlayerDoneNoZeitsteinaktionThisTurnValidator(CategoryId::INVESTITIONEN))
            ->setNext(new HasCategoryFreeZeitsteinslotsValidator(CategoryId::INVESTITIONEN))
            ->setNext(new HasPlayerEnoughResourcesValidator(new ResourceChanges(guthabenChange: new MoneyAmount(-1 * $this->amount * $this->sharePrice->value))));

        return $validationChain->validate($gameEvents, $playerId);
    }

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($playerId, $gameEvents);
        if (!$result->canExecute) {
            throw new \RuntimeException('' . $result->reason, 1752066529);
        }

        $resourceChanges = new ResourceChanges(
            guthabenChange: new MoneyAmount(-1 * ($this->sharePrice->value * $this->amount)),
            zeitsteineChange: -1,
        );

        return GameEventsToPersist::with(
            new StocksWereBoughtForPlayer(
                playerId: $playerId,
                stockType: $this->stockType,
                sharePrice: $this->sharePrice,
                amount: $this->amount,
                stockPrices: StockPriceState::calculateStockPrices($gameEvents),
                resourceChanges: $resourceChanges,
            )
        );
    }
}
