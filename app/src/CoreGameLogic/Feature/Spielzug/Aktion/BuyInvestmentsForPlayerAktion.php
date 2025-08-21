<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\DoesNotSkipTurnValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasCategoryFreeZeitsteinslotsValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerDoneNoZeitsteinaktionThisTurnValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerEnoughResourcesValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerEnoughZeitsteineValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\IsPlayerAllowedToInvestValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\IsPlayersTurnValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\InvestmentsWereBoughtForPlayer;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Investments\ValueObject\InvestmentId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

class BuyInvestmentsForPlayerAktion extends Aktion
{

    public function __construct(
        private readonly InvestmentId $investmentId,
        private readonly MoneyAmount  $price,
        private readonly int          $amount,
    )
    {
    }

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $validationChain = new IsPlayersTurnValidator();
        $validationChain
            ->setNext(new DoesNotSkipTurnValidator())
            ->setNext(new IsPlayerAllowedToInvestValidator())
            ->setNext(new HasPlayerEnoughZeitsteineValidator(1))
            ->setNext(new HasPlayerDoneNoZeitsteinaktionThisTurnValidator(CategoryId::INVESTITIONEN))
            ->setNext(new HasCategoryFreeZeitsteinslotsValidator(CategoryId::INVESTITIONEN))
            ->setNext(new HasPlayerEnoughResourcesValidator(new ResourceChanges(guthabenChange: new MoneyAmount(-1 * $this->amount * $this->price->value))));

        return $validationChain->validate($gameEvents, $playerId);
    }

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($playerId, $gameEvents);
        if (!$result->canExecute) {
            throw new \RuntimeException('' . $result->reason, 1752066529);
        }

        $resourceChanges = new ResourceChanges(
            guthabenChange: new MoneyAmount(-1 * ($this->price->value * $this->amount)),
            zeitsteineChange: -1,
        );

        return GameEventsToPersist::with(
            new InvestmentsWereBoughtForPlayer(
                playerId: $playerId,
                investmentId: $this->investmentId,
                price: $this->price,
                amount: $this->amount,
                resourceChanges: $resourceChanges,
            )
        );
    }
}
