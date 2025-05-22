<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

trait CardTrait
{
    public ?string $activeCardId = null;

    // TODO rename tob showCardActions or something
    public function activateCard(string $cardId): void
    {
        if ($this->activeCardId === $cardId) {
            $this->activeCardId = null;
        } else {
            $this->activeCardId = $cardId;
        }
    }

    public function cardIsActive(string $cardId): bool
    {
        // todo check if requirements are met
        return $this->activeCardId === $cardId;
    }

    // TODO rename to activateCard
    public function playCard(string $cardId): void
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
