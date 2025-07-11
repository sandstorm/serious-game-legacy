<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

final class HasPlayerEnoughResourcesForLebenszielphasenChangeValidator extends AbstractValidator
{

    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        $currentLebensziel = PreGameState::lebenszielForPlayerOrNull($gameEvents, $playerId);

        if ($currentLebensziel === null) {
            return new AktionValidationResult(
                canExecute: false,
                reason: "Du hast kein Lebensziel ausgewählt",
            );
        }

        $currentLebenszielPhase = $currentLebensziel->phaseDefinitions[PlayerState::getCurrentLebenszielphaseDefinitionForPlayer($gameEvents, $playerId)->phase - 1];

        $playerResources = PlayerState::getResourcesForPlayer($gameEvents, $playerId);
        if ($currentLebenszielPhase->bildungsKompetenzSlots > $playerResources->bildungKompetenzsteinChange) {
            return new AktionValidationResult(
                canExecute: false,
                reason: "Du hast nicht genug Kompetenzsteine in " . CategoryId::BILDUNG_UND_KARRIERE->value,
            );
        }
        if ($currentLebenszielPhase->freizeitKompetenzSlots > $playerResources->freizeitKompetenzsteinChange) {
            return new AktionValidationResult(
                canExecute: false,
                reason: "Du hast nicht genug Kompetenzsteine in " . CategoryId::SOZIALES_UND_FREIZEIT->value,
            );
        }

        if ($currentLebenszielPhase->investitionen > $playerResources->guthabenChange->value) {
            return new AktionValidationResult(
                canExecute: false,
                reason: "Du hast nicht genug Geld",
            );
        }

        $currentJob = PlayerState::getJobForPlayer($gameEvents, $playerId);
        if (
            $currentLebenszielPhase->erwerbseinkommen > 0 &&
            ($currentJob === null || $currentLebenszielPhase->erwerbseinkommen > $currentJob->gehalt->value)
        ) {
            return new AktionValidationResult(
                canExecute: false,
                reason: "Dein Gehalt ist zu niedrig",
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
