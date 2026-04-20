<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\TransactionEntry;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasBoughtImmobilie;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasBoughtInvestment;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasSoldImmobilie;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasSoldImmobilieToAvoidInsolvenz;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasSoldInvestment;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasSoldInvestmentsAfterInvestmentByAnotherPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasSoldInvestmentsToAvoidInsolvenz;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SpielzugWasEnded;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\ImmobilienCardDefinition;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Investments\ValueObject\InvestmentId;

class TransactionHistoryState
{
    private static function getIconClassForInvestmentId(InvestmentId $investmentId): string
    {
        return match ($investmentId) {
            InvestmentId::MERFEDES_PENZ, InvestmentId::BETA_PEAR => 'icon-aktien',
            InvestmentId::ETF_MSCI_WORLD, InvestmentId::ETF_CLEAN_ENERGY => 'icon-ETF',
            InvestmentId::BAT_COIN, InvestmentId::MEME_COIN => 'icon-krypto',
        };
    }

    /**
     * @return TransactionEntry[]
     */
    public static function getTransactionHistoryForPlayer(GameEvents $gameEvents, PlayerId $playerId): array
    {
        $entries = [];
        $turnCount = 0;
        /** @var array<string, int> $holdings */
        $holdings = [];

        foreach ($gameEvents as $event) {
            if ($event instanceof SpielzugWasEnded && $event->playerId->equals($playerId)) {
                $turnCount++;
                continue;
            }

            $currentTurn = new PlayerTurn($turnCount + 1);

            // Investment buy events
            if ($event instanceof PlayerHasBoughtInvestment && $event->playerId->equals($playerId)) {
                $assetName = $event->getInvestmentId()->value;
                $holdings[$assetName] = ($holdings[$assetName] ?? 0) + $event->amount;
                $entries[] = new TransactionEntry(
                    playerTurn: $currentTurn,
                    iconClass: self::getIconClassForInvestmentId($event->getInvestmentId()),
                    assetName: $assetName,
                    amount: $event->amount,
                    price: $event->price,
                    type: 'Kauf',
                    holdingAfter: $holdings[$assetName],
                );
                continue;
            }

            // Investment sell events (manual)
            if ($event instanceof PlayerHasSoldInvestment && $event->playerId->equals($playerId)) {
                $assetName = $event->getInvestmentId()->value;
                $holdings[$assetName] = ($holdings[$assetName] ?? 0) - $event->amount;
                $entries[] = new TransactionEntry(
                    playerTurn: $currentTurn,
                    iconClass: self::getIconClassForInvestmentId($event->getInvestmentId()),
                    assetName: $assetName,
                    amount: $event->amount,
                    price: $event->price,
                    type: 'Verkauf',
                    holdingAfter: $holdings[$assetName],
                );
                continue;
            }

            // Investment sell after another player's purchase
            if ($event instanceof PlayerHasSoldInvestmentsAfterInvestmentByAnotherPlayer && $event->playerId->equals($playerId)) {
                $assetName = $event->getInvestmentId()->value;
                $holdings[$assetName] = ($holdings[$assetName] ?? 0) - $event->amount;
                $entries[] = new TransactionEntry(
                    playerTurn: $currentTurn,
                    iconClass: self::getIconClassForInvestmentId($event->getInvestmentId()),
                    assetName: $assetName,
                    amount: $event->amount,
                    price: $event->price,
                    type: 'Verkauf',
                    holdingAfter: $holdings[$assetName],
                );
                continue;
            }

            // Investment sell to avoid insolvency
            if ($event instanceof PlayerHasSoldInvestmentsToAvoidInsolvenz && $event->getPlayerId()->equals($playerId)) {
                $assetName = $event->getInvestmentId()->value;
                $amount = $event->getAmount();
                $holdings[$assetName] = ($holdings[$assetName] ?? 0) - $amount;
                $totalReceived = $event->getResourceChanges($playerId)->guthabenChange;
                $pricePerUnit = $amount > 0 ? new MoneyAmount($totalReceived->value / $amount) : new MoneyAmount(0);
                $entries[] = new TransactionEntry(
                    playerTurn: $currentTurn,
                    iconClass: self::getIconClassForInvestmentId($event->getInvestmentId()),
                    assetName: $assetName,
                    amount: $amount,
                    price: $pricePerUnit,
                    type: 'Verkauf',
                    holdingAfter: $holdings[$assetName],
                );
                continue;
            }

            // Immobilie buy
            if ($event instanceof PlayerHasBoughtImmobilie && $event->getPlayerId()->equals($playerId)) {
                $cardDef = CardFinder::getInstance()->getCardById($event->getCardId(), ImmobilienCardDefinition::class);
                $assetName = 'Immobilie: ' . $cardDef->getTitle();
                $holdings[$assetName] = ($holdings[$assetName] ?? 0) + 1;
                $price = $event->getResourceChanges($playerId)->guthabenChange->negate();
                $entries[] = new TransactionEntry(
                    playerTurn: $currentTurn,
                    iconClass: 'icon-immobilien',
                    assetName: $assetName,
                    amount: 1,
                    price: $price,
                    type: 'Kauf',
                    holdingAfter: $holdings[$assetName],
                );
                continue;
            }

            // Immobilie sell (manual)
            if ($event instanceof PlayerHasSoldImmobilie && $event->getPlayerId()->equals($playerId)) {
                $cardDef = CardFinder::getInstance()->getCardById($event->getCardId(), ImmobilienCardDefinition::class);
                $assetName = 'Immobilie: ' . $cardDef->getTitle();
                $holdings[$assetName] = ($holdings[$assetName] ?? 0) - 1;
                $price = $event->getResourceChanges($playerId)->guthabenChange;
                $entries[] = new TransactionEntry(
                    playerTurn: $currentTurn,
                    iconClass: 'icon-immobilien',
                    assetName: $assetName,
                    amount: 1,
                    price: $price,
                    type: 'Verkauf',
                    holdingAfter: $holdings[$assetName],
                );
                continue;
            }

            // Immobilie sell to avoid insolvency
            if ($event instanceof PlayerHasSoldImmobilieToAvoidInsolvenz && $event->getPlayerId()->equals($playerId)) {
                $cardDef = CardFinder::getInstance()->getCardById($event->getCardId(), ImmobilienCardDefinition::class);
                $assetName = 'Immobilie: ' . $cardDef->getTitle();
                $holdings[$assetName] = ($holdings[$assetName] ?? 0) - 1;
                $price = $event->getResourceChanges($playerId)->guthabenChange;
                $entries[] = new TransactionEntry(
                    playerTurn: $currentTurn,
                    iconClass: 'icon-immobilien',
                    assetName: $assetName,
                    amount: 1,
                    price: $price,
                    type: 'Verkauf',
                    holdingAfter: $holdings[$assetName],
                );
                continue;
            }
        }

        return $entries;
    }
}
