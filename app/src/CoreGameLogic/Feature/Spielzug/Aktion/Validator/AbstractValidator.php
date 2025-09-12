<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;


use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\PlayerId;

/**
 * This is the base class for our Validators. Extend this in your Validator.
 * We use the chain-of-responsibilities pattern: https://refactoring.guru/design-patterns/chain-of-responsibility
 *
 * @see Validator
 * @see IsPlayersTurnValidator for an example on how to use this
 */
abstract class AbstractValidator implements Validator
{

    private Validator|null $nextValidator = null;

    public final function setNext(Validator $validator): Validator
    {
        $this->nextValidator = $validator;
        return $validator;
    }

    public final function append(Validator $validator): Validator
    {
        if ($this->nextValidator === null) {
            return $this->setNext($validator);
        }
        return $this->nextValidator->append($validator);
    }

    /**
     * This function must be overridden by your validator. If the validation fails your should return an
     * AktionValidationResult with the reason. If the validation is successful you should always return
     * `parent::validate()`.
     *
     * This function will then be the last in the chain and return a successful AktionValidationResult if all
     * previous Validators succeeded.
     *
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @return AktionValidationResult
     */
    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        if ($this->nextValidator !== null) {
            return $this->nextValidator->validate($gameEvents, $playerId);
        }

        return new AktionValidationResult(
            canExecute: true
        );
    }
}
