<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\ImmobilienPriceState;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerImmobilieToSellValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerDoneNoZeitsteinaktionThisTurnValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerEnoughZeitsteineValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\IsPlayersTurnValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\ImmobilieWasSoldForPlayer;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\InvestitionenCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

class SellImmobilieAktion extends Aktion
{
    public function __construct(
        public CardId $cardId
    ) {
    }

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $validatorChain = new IsPlayersTurnValidator();
        $validatorChain
            ->setNext(new HasPlayerImmobilieToSellValidator($this->cardId))
            ->setNext(new HasPlayerEnoughZeitsteineValidator(1))
            ->setNext(new HasPlayerDoneNoZeitsteinaktionThisTurnValidator(CategoryId::INVESTITIONEN));
        return $validatorChain->validate($gameEvents, $playerId);
    }

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($playerId, $gameEvents);
        if (!$result->canExecute) {
            throw new \RuntimeException('' . $result->reason, 1754909475);
        }

        /** @var InvestitionenCardDefinition $immobilienCard */
        $immobilienCard = CardFinder::getInstance()->getCardById($this->cardId, InvestitionenCardDefinition::class);

        $resourceChanges = new ResourceChanges(
            guthabenChange: ImmobilienPriceState::getCurrentImmobiliePrice($gameEvents, $immobilienCard),
            zeitsteineChange: -1,
        );

        return GameEventsToPersist::with(
            new ImmobilieWasSoldForPlayer(
                playerId: $playerId,
                cardId: $this->cardId,
                resourceChanges: $resourceChanges,
            )
        );
    }
}
