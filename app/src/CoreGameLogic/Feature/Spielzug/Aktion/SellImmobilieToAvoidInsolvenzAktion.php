<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\ImmobilienPriceState;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\DoesPlayerOwnImmobilieValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerANegativeBalanceValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerCompletedMoneySheetValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasSoldImmobilieToAvoidInsolvenz;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\ImmobilieId;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

class SellImmobilieToAvoidInsolvenzAktion extends Aktion
{
    public function __construct(
        public ImmobilieId $immobilieId,
    ) {}

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $validatorChain = new HasPlayerANegativeBalanceValidator();
        $validatorChain
            ->setNext(new HasPlayerCompletedMoneySheetValidator())
            ->setNext(new DoesPlayerOwnImmobilieValidator($this->immobilieId));

        return $validatorChain->validate($gameEvents, $playerId);
    }

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($playerId, $gameEvents);
        if (!$result->canExecute) {
            throw new \RuntimeException('' . $result->reason, 1754909475);
        }

        // Selling an Immobilie to avoid Insolvenz will not return the full value
        $reducedValue = new MoneyAmount(ImmobilienPriceState::getCurrentPriceForImmobilie($gameEvents, $this->immobilieId)->value * 0.8);

        $resourceChanges = new ResourceChanges(
            guthabenChange: $reducedValue,
            zeitsteineChange: -1,
        );

        return GameEventsToPersist::with(
            new PlayerHasSoldImmobilieToAvoidInsolvenz(
                playerId: $playerId,
                immobilieId: $this->immobilieId,
                resourceChanges: $resourceChanges,
            )
        );
    }
}
