<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasNotYetAnsweredThisWeiterbildungValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerStartedAWeiterbildungThisTurnValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\IsPlayersTurnValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\SelectedAnswerForWeiterbildungExistsValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\AnswerForWeiterbildungWasSubmitted;
use Domain\CoreGameLogic\Feature\Spielzug\Event\WeiterbildungWasStarted;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\WeiterbildungCardDefinition;
use Domain\Definitions\Card\ValueObject\AnswerId;
use RuntimeException;


class SubmitAnswerForWeiterbildungAktion extends Aktion
{
    public function __construct(private readonly AnswerId $selectedAnswer)
    {
    }

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $validatorChain = new IsPlayersTurnValidator();
        $validatorChain
            ->setNext(new HasPlayerStartedAWeiterbildungThisTurnValidator())
            ->setNext(new HasNotYetAnsweredThisWeiterbildungValidator())
            ->setNext(new SelectedAnswerForWeiterbildungExistsValidator($this->selectedAnswer));

        return $validatorChain->validate($gameEvents, $playerId);
    }

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($playerId, $gameEvents);
        if (!$result->canExecute) {
            throw new RuntimeException('Cannot submit weiterbildung: ' . $result->reason, 1753265973
            );
        }

        /** @var WeiterbildungWasStarted $lastWeiterbildungWasStartedEvent */
        $lastWeiterbildungWasStartedEvent = $gameEvents->findLast(WeiterbildungWasStarted::class);

        /** @var WeiterbildungCardDefinition $weiterbildungCardDefinition */
        $weiterbildungCardDefinition = CardFinder::getInstance()->getCardById($lastWeiterbildungWasStartedEvent->weiterbildungCardId, WeiterbildungCardDefinition::class);

        return GameEventsToPersist::with(
            new AnswerForWeiterbildungWasSubmitted(
                playerId: $playerId,
                cardId: $weiterbildungCardDefinition->getId(),
                selectedAnswerId: $this->selectedAnswer,
                wasCorrect: $weiterbildungCardDefinition->getCorrectAnswerId()->equals($this->selectedAnswer)
            )
        );
    }
}

