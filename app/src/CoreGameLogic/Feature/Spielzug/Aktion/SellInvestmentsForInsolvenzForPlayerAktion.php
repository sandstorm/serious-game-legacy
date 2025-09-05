<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerANegativeBalanceValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerEnoughInvestmentsToSellValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\IsPlayerNotInsolventValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\InvestmentsWereSoldForInsolvenzForPlayer;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Investments\ValueObject\InvestmentId;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

class SellInvestmentsForInsolvenzForPlayerAktion extends Aktion
{
    public function __construct(
        private readonly InvestmentId   $investmentId,
        private readonly MoneyAmount $price,
        private readonly int         $amount
    ) {
    }

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $validationChain = new IsPlayerNotInsolventValidator();
        $validationChain
            ->setNext(new HasPlayerANegativeBalanceValidator())
            ->setNext(new HasPlayerEnoughInvestmentsToSellValidator($this->investmentId, 1))
            ->setNext(new HasPlayerEnoughInvestmentsToSellValidator($this->investmentId, $this->amount));
        return $validationChain->validate($gameEvents, $playerId);
    }

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($playerId, $gameEvents);
        if (!$result->canExecute) {
            throw new \RuntimeException('' . $result->reason, 1752753850);
        }

        return GameEventsToPersist::with(
            new InvestmentsWereSoldForInsolvenzForPlayer(
                playerId: $playerId,
                investmentId: $this->investmentId,
                price: $this->price,
                amount: $this->amount,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount($this->price->value * $this->amount),
                )
            )
        );
    }
}
