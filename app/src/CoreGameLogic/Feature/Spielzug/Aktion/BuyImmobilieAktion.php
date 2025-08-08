<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerDoneNoZeitsteinaktionThisTurnValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerEnoughResourcesValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerEnoughZeitsteineValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\IsPlayersTurnValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\ImmobilieWasBoughtForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\InvestitionenCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

class BuyImmobilieAktion extends Aktion
{
    public function __construct(
        public CardId $cardId
    ) {
    }

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $immobilienCard = CardFinder::getInstance()->getCardById($this->cardId, InvestitionenCardDefinition::class);

        $validatorChain = new IsPlayersTurnValidator();
        $validatorChain
            ->setNext(new HasPlayerEnoughResourcesValidator($immobilienCard->getResourceChanges()))
            ->setNext(new HasPlayerEnoughZeitsteineValidator(1))
            ->setNext(new HasPlayerDoneNoZeitsteinaktionThisTurnValidator(CategoryId::INVESTITIONEN));
        return $validatorChain->validate($gameEvents, $playerId);
    }

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($playerId, $gameEvents);
        if (!$result->canExecute) {
            throw new \RuntimeException('' . $result->reason, 1754661378);
        }

        $immobilienCard = CardFinder::getInstance()->getCardById($this->cardId, InvestitionenCardDefinition::class);

        $resourceChanges = new ResourceChanges(
            guthabenChange: $immobilienCard->getResourceChanges()->guthabenChange,
            zeitsteineChange: -1,
        );

        $pileId = new PileId(
            $immobilienCard->getCategory(),
            PlayerState::getCurrentLebenszielphaseIdForPlayer($gameEvents, $playerId)
        );

        return GameEventsToPersist::with(
            new ImmobilieWasBoughtForPlayer(
                playerId: $playerId,
                cardId: $immobilienCard->getId(),
                pileId: $pileId,
                resourceChanges: $resourceChanges,
            )
        );
    }
}
