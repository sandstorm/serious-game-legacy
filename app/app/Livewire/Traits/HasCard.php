<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ShuffleCards;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SkipCard;
use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\PileId;

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

    /**
     * @param string $cardId
     * @return bool
     */
    public function canActivateCard(string $cardId): bool
    {
        $card = CardFinder::getCardById(CardId::fromString($cardId));
        return AktionsCalculator::forStream($this->gameStream)->canPlayerAffordToActivateCard($this->myself, $card);
    }

    /**
     * @return bool
     */
    public function canSkipCard(): bool
    {
        $canAffordToSkip = AktionsCalculator::forStream($this->gameStream)->canPlayerAffordToSkipCard($this->myself);
        $usedSkipThisTurn = AktionsCalculator::forStream($this->gameStream)->hasPlayerSkippedACardThisRound($this->myself);
        return $canAffordToSkip && !$usedSkipThisTurn;
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
            PileId::from($pileId)
        ));
        $this->broadcastNotify();
    }

    /**
     * @param string $cardId
     * @param string $pileId
     * @return void
     */
    public function skipCard(string $cardId, string $pileId): void
    {
        if (!self::canSkipCard()) {
            // TODO show error message why
            return;
        }
        $this->coreGameLogic->handle($this->gameId, new SkipCard(
            $this->myself,
            CardId::fromString($cardId),
            PileId::from($pileId)
        ));
        $this->broadcastNotify();
    }

    // TODO for testing, is not in final game
    public function shuffleCards(): void
    {
        // @phpstan-ignore staticMethod.deprecatedClass
        $this->coreGameLogic->handle($this->gameId, ShuffleCards::create());
        $this->broadcastNotify();
    }
}
