<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\ValueObject\NotificationTypeEnum;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\ActivateCardAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\SkipCardAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SkipCard;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

trait HasCard
{
    public ?string $showCardActionsForCard = null;

    /**
     * @param string $cardId
     * @return void
     */
    public function showCardActions(string $cardId): void
    {
        if ($this->showCardActionsForCard === $cardId) {
            $this->showCardActionsForCard = null;
        } else {
            $this->showCardActionsForCard = $cardId;
        }
    }

    /**
     * @param string $cardId
     * @return bool
     */
    public function cardActionsVisible(string $cardId): bool
    {
        return $this->showCardActionsForCard === $cardId && $this->currentPlayerIsMyself();
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
