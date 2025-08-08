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
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\IsImmobilieCurrentlyAvailableValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\IsPlayerAllowedToInvestValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\IsPlayersTurnValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasBoughtImmobilie;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\ImmobilieId;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\ImmobilienCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

class BuyImmobilieAktion extends Aktion
{
    private ImmobilienCardDefinition $immobilienCard;

    public function __construct(
        public CardId $cardId
    ) {
        $this->immobilienCard = CardFinder::getInstance()->getCardById($cardId, ImmobilienCardDefinition::class);
    }

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $validatorChain = new IsPlayersTurnValidator();

        $validatorChain
            ->setNext(new DoesNotSkipTurnValidator())
            ->setNext(new IsPlayerAllowedToInvestValidator())
            ->setNext(new HasPlayerEnoughZeitsteineValidator(1))
            ->setNext(new HasPlayerDoneNoZeitsteinaktionThisTurnValidator(CategoryId::INVESTITIONEN))
            ->setNext(new HasCategoryFreeZeitsteinslotsValidator(CategoryId::INVESTITIONEN))
            ->setNext(new IsImmobilieCurrentlyAvailableValidator($this->cardId))
            ->setNext(new HasPlayerEnoughResourcesValidator($this->immobilienCard->getResourceChanges()));

        return $validatorChain->validate($gameEvents, $playerId);
    }

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($playerId, $gameEvents);
        if (!$result->canExecute) {
            throw new \RuntimeException('' . $result->reason, 1754661378);
        }

        $resourceChanges = new ResourceChanges(
            guthabenChange: $this->immobilienCard->getResourceChanges()->guthabenChange,
            zeitsteineChange: -1,
        );

        $pileId = new PileId(
            $this->immobilienCard->getCategory(),
            PlayerState::getCurrentLebenszielphaseIdForPlayer($gameEvents, $playerId)
        );

        $immoblieId = new ImmobilieId(
            cardId: $this->immobilienCard->getId(),
            playerTurn: PlayerState::getCurrentTurnForPlayer($gameEvents, $playerId),
        );

        return GameEventsToPersist::with(
            new PlayerHasBoughtImmobilie(
                playerId: $playerId,
                immobilieId: $immoblieId,
                cardId: $this->immobilienCard->getId(),
                pileId: $pileId,
                resourceChanges: $resourceChanges,
            )
        );
    }
}
