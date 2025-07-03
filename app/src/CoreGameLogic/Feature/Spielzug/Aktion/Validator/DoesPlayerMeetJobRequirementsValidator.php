<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\ValueObject\CardId;

/**
 * Succeeds if the player has enough Kompetenzsteine to fulfill the jobs requirements
 */
final class DoesPlayerMeetJobRequirementsValidator extends AbstractValidator
{
    private CardId $jobId;

    public function __construct(CardId $jobId)
    {
        $this->jobId = $jobId;
    }

    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        /** @var JobCardDefinition $jobCard */
        $jobCard = CardFinder::getInstance()->getCardById($this->jobId);
        if (!AktionsCalculator::forStream($gameEvents)->canPlayerAffordJobCard($playerId, $jobCard)) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du erfüllst nicht die Voraussetzungen für diesen Job',
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
