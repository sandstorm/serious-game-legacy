<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\ValueObject\NotificationTypeEnum;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\PileState;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\ActivateCardAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\SkipCardAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\PutCardBackOnTopOfPile;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SkipCard;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasActivated;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasSkipped;
use Domain\CoreGameLogic\Feature\Spielzug\Event\EreignisWasTriggered;
use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\EreignisCardDefinition;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

trait HasCard
{
    public ?string $showCardActionsForCard = null;
    public bool $isEreignisCardVisible = false;
    public bool $playerHasToPlayCard = false;
    public ?string $ereignisCardDefinition = null;

    /**
     * Update the form state on a rendering. Can happen for example when user changes their job.
     * Rerendering is triggered by Livewire when we use the broadcastNotify() method.
     *
     * @return void
     */
    public function renderingHasCard(): void
    {
        if (PreGameState::isInPreGamePhase($this->gameEvents)) {
            return;
        }

        $this->playerHasToPlayCard = false;

        // if player skipped a card, we show the next card from the top of the pile
        $aktionsCalculator = AktionsCalculator::forStream($this->gameEvents);
        if ($aktionsCalculator->hasPlayerSkippedACardThisRound($this->myself) && !$aktionsCalculator->hasPlayerPlayedACardOrPutOneBack($this->myself)) {
            /** @var CardWasSkipped $cardWasSkipped */
            $cardWasSkipped = $this->gameEvents->findLast(CardWasSkipped::class);
            $pileId = new PileId($cardWasSkipped->getCategoryId(), PlayerState::getCurrentLebenszielphaseIdForPlayer($this->gameEvents, $this->myself));

            // get next card from top and show it
            $topCardIdForPile = PileState::topCardIdForPile($this->gameEvents, $pileId);
            $this->showCardActionsForCard = $topCardIdForPile->value;
            $this->playerHasToPlayCard = true;
        }
    }

    /**
     * @param string $cardId
     * @return void
     */
    public function showCardActions(string $cardId): void
    {
        $this->showCardActionsForCard = $cardId;
    }

    public function closeCardActions(): void
    {
        if ($this->playerHasToPlayCard) {
            return;
        }
        $this->showCardActionsForCard = null;
    }

    /**
     * @param string $cardId
     * @return bool
     */
    public function cardActionsVisible(string $cardId): bool
    {
        return $this->showCardActionsForCard === $cardId;
    }

    public function closeEreignisCard(): void
    {
        $this->isEreignisCardVisible = false;
    }

    public function canActivateCard(string $category): AktionValidationResult
    {
        $aktion = new ActivateCardAktion(CategoryId::from($category));
        return $aktion->validate($this->myself, $this->gameEvents);
    }

    public function canSkipCard(string $category): AktionValidationResult
    {
        $aktion = new SkipCardAktion(CategoryId::from($category));
        return $aktion->validate($this->myself, $this->gameEvents);
    }

    /**
     * @param string $category
     * @return void
     */
    public function activateCard(string $category): void
    {
        $validationResult = self::canActivateCard($category);
        if (!$validationResult->canExecute) {
            $this->showNotification(
                $validationResult->reason,
                NotificationTypeEnum::ERROR
            );
            return;
        }

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            $this->myself,
            CategoryId::from($category)
        ));

        // WHY: refresh the gameEvents - otherwise the modal for the last Ereignis will not be rendered correctly
        $this->gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $eventsAfterActivateCard = $this->gameEvents->findAllAfterLastOfType(CardWasActivated::class);
        /** @var EreignisWasTriggered|null $ereignisOrNull */
        $ereignisOrNull = $eventsAfterActivateCard->findLastOrNull(EreignisWasTriggered::class);
        if ($ereignisOrNull !== null) {
            $ereignisCardDefinition = CardFinder::getInstance()->getCardById($ereignisOrNull->ereignisCardId, EreignisCardDefinition::class);
            $this->ereignisCardDefinition = $ereignisCardDefinition->getDescription();
            $this->isEreignisCardVisible = true;
        }
        $this->broadcastNotify();
    }

    /**
     * @param string $category
     * @return void
     */
    public function skipCard(string $category): void
    {
        $validationResult = self::canSkipCard($category);
        if (!$validationResult->canExecute) {
            $this->showNotification(
                $validationResult->reason,
                NotificationTypeEnum::ERROR
            );
            return;
        }
        $this->coreGameLogic->handle($this->gameId, new SkipCard(
            $this->myself,
            CategoryId::from($category)
        ));

        $this->broadcastNotify();
    }

    /**
     * Put card back on top of the pile.
     * If player has skipped a card this turn and cannot afford the top card on the pile.
     *
     * @param string $category
     * @return void
     */
    public function putCardBackOnTopOfPile(string $category): void
    {
        $this->coreGameLogic->handle($this->gameId, new PutCardBackOnTopOfPile($this->myself, CategoryId::from($category)));
        $this->broadcastNotify();
    }
}
