<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\ValueObject\NotificationTypeEnum;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\ActivateCardAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\SkipCardAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SkipCard;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasActivated;
use Domain\CoreGameLogic\Feature\Spielzug\Event\EreignisWasTriggered;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\EreignisCardDefinition;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

trait HasCard
{
    public ?string $showCardActionsForCard = null;
    public bool $isEreignisCardVisible = false;
    public ?string $ereignisCardDefinition = null;

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
        $this->showCardActionsForCard = null;
    }

    /**
     * @param string $cardId
     * @return bool
     */
    public function cardActionsVisible(string $cardId): bool
    {
        return $this->showCardActionsForCard === $cardId && $this->currentPlayerIsMyself();
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
            $this->ereignisCardDefinition = $ereignisCardDefinition->description();
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
}
