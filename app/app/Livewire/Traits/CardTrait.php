<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

trait CardTrait
{
    public ?string $showCardActionsForCard = null;

    public function showCardActions(string $cardId): void
    {
        if ($this->showCardActionsForCard === $cardId) {
            $this->showCardActionsForCard = null;
        } else {
            $this->showCardActionsForCard = $cardId;
        }
    }

    public function cardActionsVisible(string $cardId): bool
    {
        // todo check if requirements are met
        return $this->showCardActionsForCard === $cardId;
    }

    public function activateCard(string $cardId): void
    {
        // TODO implement this
        // $this->gameStream->playCard($cardId);
    }

    public function skipCard(string $cardId): void
    {
        // only one card can skipped, the next needs to be played if possible
        // TODO implement this
        // $this->gameStream->skipCard($cardId);
    }

}
