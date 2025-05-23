<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use Domain\CoreGameLogic\Dto\ValueObject\CardId;
use Domain\CoreGameLogic\Dto\ValueObject\PileId;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SkipCard;
use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\Definitions\Cards\CardFinder;

trait CardTrait
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
        return $this->showCardActionsForCard === $cardId;
    }

    /**
     * @param string $cardId
     * @return bool
     */
    public function canActivateCard(string $cardId): bool
    {
        $card = CardFinder::getCardById(CardId::fromString($cardId));
        return AktionsCalculator::forStream($this->gameStream)->canPlayerActivateCard($this->myself, $card);
    }

    /**
     * @param string $cardId
     * @return bool
     */
    public function canSkipCard(string $cardId): bool
    {
        $card = CardFinder::getCardById(CardId::fromString($cardId));
        return AktionsCalculator::forStream($this->gameStream)->canPlayerSkipCard($this->myself, $card);
    }

    /**
     * @param string $cardId
     * @param string $pileId
     * @return void
     */
    public function activateCard(string $cardId, string $pileId): void
    {
        if (!self::canActivateCard($cardId)) {
            // TODO show error message why
            return;
        }

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            $this->myself,
            CardId::fromString($cardId),
            PileId::fromString($pileId)
        ));
    }

    /**
     * @param string $cardId
     * @param string $pileId
     * @return void
     */
    public function skipCard(string $cardId, string $pileId): void
    {
        if (!self::canSkipCard($cardId)) {
            // TODO show error message why
            return;
        }
        $this->coreGameLogic->handle($this->gameId, new SkipCard(
            $this->myself,
            CardId::fromString($cardId),
            PileId::fromString($pileId)
        ));
    }

}
