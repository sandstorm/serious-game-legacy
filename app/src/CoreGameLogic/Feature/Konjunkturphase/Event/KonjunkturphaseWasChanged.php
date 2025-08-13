<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\InvestmentPrice;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior\ProvidesInvestmentPriceChanges;
use Domain\Definitions\Investments\ValueObject\InvestmentId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphasenId;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphaseTypeEnum;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;

final readonly class KonjunkturphaseWasChanged implements GameEventInterface, ProvidesInvestmentPriceChanges
{
    /**
     * @param InvestmentPrice[] $investmentPrices
     */
    public function __construct(
        public KonjunkturphasenId      $id,
        public Year                    $year,
        public KonjunkturphaseTypeEnum $type,
        public array                   $investmentPrices
    )
    {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            id: KonjunkturphasenId::create($values['id']),
            year: new Year($values['year']),
            type: KonjunkturphaseTypeEnum::fromString($values['type']),
            investmentPrices: array_map(
                static fn($investmentPrice) => InvestmentPrice::fromArray($investmentPrice),
                $values['investmentPrices']
            ),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id->jsonSerialize(),
            'year' => $this->year->jsonSerialize(),
            'type' => $this->type,
            'investmentPrices' => $this->investmentPrices,
        ];
    }

    public function getInvestmentPrice(InvestmentId $investmentId): MoneyAmount
    {
        foreach ($this->investmentPrices as $investmentPrice) {
            if ($investmentPrice->investmentId === $investmentId) {
                return $investmentPrice->price;
            }
        }
        throw new \DomainException('Investment price not found for: ' . $investmentId->value, 1752584032);
    }
}
