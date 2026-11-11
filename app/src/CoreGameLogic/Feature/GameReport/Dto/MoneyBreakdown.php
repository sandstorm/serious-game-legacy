<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\GameReport\Dto;

use Domain\Definitions\Card\ValueObject\MoneyAmount;

/**
 * The summed up Guthaben changes of a player per category. Income is positive, expenses are negative,
 * so the total is the final Guthaben of the player.
 */
final readonly class MoneyBreakdown
{
    /**
     * @param array<string, float> $amountsByCategory keyed by {@see MoneyCategory::$value}
     */
    public function __construct(
        private array $amountsByCategory,
    ) {
    }

    public function get(MoneyCategory $category): MoneyAmount
    {
        return new MoneyAmount($this->amountsByCategory[$category->value] ?? 0);
    }

    public function getTotal(): MoneyAmount
    {
        return new MoneyAmount(array_sum($this->amountsByCategory));
    }

    /**
     * @return BreakdownEntry[] all categories with a positive sum
     */
    public function getIncome(): array
    {
        return array_values(array_filter($this->getEntries(), fn (BreakdownEntry $entry) => $entry->amount->value > 0));
    }

    /**
     * @return BreakdownEntry[] all categories with a negative sum
     */
    public function getExpenses(): array
    {
        return array_values(array_filter($this->getEntries(), fn (BreakdownEntry $entry) => $entry->amount->value < 0));
    }

    /**
     * @return BreakdownEntry[]
     */
    private function getEntries(): array
    {
        return array_map(
            fn (MoneyCategory $category) => new BreakdownEntry($category, $this->get($category)),
            MoneyCategory::cases()
        );
    }
}
