<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ZeitsteinAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasSkipped;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SpielzugWasEnded;
use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

/**
 * Succeeds if:
 *  - the player has not done any Zeitsteinaktion this turn
 *  - OR the Zeitsteinaktion was a SkipCard action and the current action is an ActivateCard action on the same pile
 */
final class HasPlayerDoneNoZeitsteinaktionThisTurnValidator extends AbstractValidator
{
    private CategoryId | null $categoryId = null;
    private bool $isActivateCardAktion = false;

    public function __construct(
        ?CategoryId $categoryId = null,
        bool $isActivateCardAktion = false,
    )
    {
        $this->categoryId = $categoryId;
        $this->isActivateCardAktion = $isActivateCardAktion;
    }

    public static function withSpecialRulesForActivateCard(CategoryId $categoryId): HasPlayerDoneNoZeitsteinaktionThisTurnValidator
    {
        return new self($categoryId, true);
    }

    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        if ($this->isActivateCardAktion) { // Special case -> you are allowed to skip a card in the same category
            return $this->validateForActivateCardAktion($gameEvents, $playerId);
        }

        $eventsThisTurn = $gameEvents->findAllAfterLastOfTypeOrNull(SpielzugWasEnded::class) ?? $gameEvents->findAllAfterLastOfType(GameWasStarted::class);
        $zeitsteinEventsThisTurn = $eventsThisTurn->findAllOfType(ZeitsteinAktion::class);
        if (count($zeitsteinEventsThisTurn) > 0) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du kannst nur eine Zeitsteinaktion pro Runde ausführen',
            );
        }

        return parent::validate($gameEvents, $playerId);
    }

    /**
     * This is a special case. Players are allowed to skip a card and activate the next card on the same pile.
     *
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @return AktionValidationResult
     */
    private function validateForActivateCardAktion(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        if ($this->categoryId === null) {
            // This should not happen, since validateForActivateCardAktion only is called when the category is provided
            throw new \RuntimeException("CategoryId must not be null", 1751616354);
        }

        $eventsThisTurn = AktionsCalculator::forStream($gameEvents)->getEventsThisTurn();
        $zeitsteinEventsThisTurn = $eventsThisTurn->findAllOfType(ZeitsteinAktion::class);
        if (count($zeitsteinEventsThisTurn) === 0) {
            return parent::validate($gameEvents, $playerId);
        }

        if (count($zeitsteinEventsThisTurn) > 1 || $zeitsteinEventsThisTurn->findFirstOrNull(CardWasSkipped::class) === null) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du hast bereits eine andere Aktion ausgeführt'
            );
        }

        if ($zeitsteinEventsThisTurn->findFirst(CardWasSkipped::class)->getCategoryId()->value !== $this->categoryId->value) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du hast bereits eine Karte in einer anderen Kategorie übersprungen'
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
