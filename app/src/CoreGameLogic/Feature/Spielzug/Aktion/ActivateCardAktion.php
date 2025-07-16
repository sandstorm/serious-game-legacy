<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\PileState;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\CanPlayerAffordTopCardOnPileValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasCategoryFreeZeitsteinslotsValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerDoneNoZeitsteinaktionThisTurnValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\IsPlayersTurnValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Command\MaybeTriggerEreignis;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\EreignisCommandHandler;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasActivated;
use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\CardDefinition;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use RuntimeException;

class ActivateCardAktion extends Aktion
{
    private PileId $pileId;

    public function __construct(public CategoryId $category)
    {
        parent::__construct('activate-card', 'Karte Spielen');

        // TODO reorganize piles -> Category and phase separate -> `PileState::topCardIdForPile($gameEvents, PileId::BILDUNG, phase: 1)`
        $this->pileId = PileState::getPileIdForCategoryAndPhase($category);
    }


    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $validationChain = new IsPlayersTurnValidator();
        $validationChain
            ->setNext(new CanPlayerAffordTopCardOnPileValidator($this->pileId))
            ->setNext(HasPlayerDoneNoZeitsteinaktionThisTurnValidator::withSpecialRulesForActivateCard($this->category))
            ->setNext(new HasCategoryFreeZeitsteinslotsValidator($this->category));

        return $validationChain->validate($gameEvents, $playerId);
    }

    private function getTotalCosts(
        GameEvents $gameEvents,
        PlayerId $player,
        CardDefinition $cardDefinition
    ): ResourceChanges {
        $costToActivate = new ResourceChanges(
            zeitsteineChange: AktionsCalculator::forStream($gameEvents)->hasPlayerSkippedACardThisRound($player) ? 0 : -1
        );
        return $cardDefinition instanceof KategorieCardDefinition ? $costToActivate->accumulate($cardDefinition->resourceChanges) : $costToActivate;
    }

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($playerId, $gameEvents);
        if (!$result->canExecute) {
            throw new RuntimeException('Cannot activate Card: ' . $result->reason, 1748951140);
        }
        $topCardOnPile = PileState::topCardIdForPile($gameEvents, $this->pileId);
        $cardDefinition = CardFinder::getInstance()->getCardById($topCardOnPile);
        $eventsFromActivation = GameEventsToPersist::with(
            new CardWasActivated(
                $playerId,
                $this->pileId,
                $cardDefinition->getId(),
                $this->category,
                $this->getTotalCosts($gameEvents, $playerId, $cardDefinition),
                AktionsCalculator::forStream($gameEvents)->hasPlayerSkippedACardThisRound($playerId) ? 0 : 1,
            )
        );

        // Append Ereignis-Events (this has a 25 percent chance to be triggered, otherwise nothing gets added)
        return $eventsFromActivation->withAppendedEvents(
            ...(new EreignisCommandHandler())->handle(MaybeTriggerEreignis::create($playerId), $gameEvents)
        );
    }
}
