<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\PlayerId;

/**
 * This interface is implemented by the AbstractValidator and describes the API for
 * our validation chain.
 * We use the chain-of-responsibilities pattern: https://refactoring.guru/design-patterns/chain-of-responsibility
 *
 * @see AbstractValidator
 */
interface Validator
{
    /**
     * This function is used to add a Validator to the chain.
     * @param Validator $validator
     * @return Validator
     */
    public function setNext(Validator $validator): Validator;

    /**
     * This function handles the validation. If the validation fails, it will return an AktionValidationResult with
     * the reason for the failure. If the validation is successful it will call the parents validate() function.
     * If all Validators in the chain run successfully, the AbstractValidator will return an AktionValidationResult with
     * `canExecute => true`.
     *
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @return AktionValidationResult
     */
    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult;
}
