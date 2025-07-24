<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\WeiterbildungCardDefinition;
use Domain\Definitions\Card\ValueObject\AnswerId;

final class SelectedAnswerForWeiterbildungExistsValidator extends AbstractValidator
{
    public function __construct(
        public AnswerId $selectedAnswer,
    )
    {
    }

    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        $answerOptions = PlayerState::getLastWeiterbildungCardDefinitionForPlayer($gameEvents, $playerId)?->getAnswerOptions();
        $selectedAnswerId = $this->selectedAnswer->value;

        if ($answerOptions === null) {
            /**
             * This should not happen unless @see HasPlayerStartedAWeiterbildungThisTurnValidator does not work or
             * if there are no answer options in the @see WeiterbildungCardDefinition or something else entirely.
             * Anyways we want to throw here, since we do not know what went wrong.
             */
            throw new \RuntimeException('No answer options were found.', 1753344631);
        }

        foreach ($answerOptions as $option) {
            if ($option->id->value === $selectedAnswerId) {
                return parent::validate($gameEvents, $playerId);
            }
        }

        return new AktionValidationResult(
            canExecute: false,
            reason: 'Die gewählte Antwort existiert nicht.'
        );
    }
}
