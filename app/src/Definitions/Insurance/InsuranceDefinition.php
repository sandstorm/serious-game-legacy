<?php

declare(strict_types=1);

namespace Domain\Definitions\Insurance;

use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Insurance\ValueObject\InsuranceId;
use Domain\Definitions\Insurance\ValueObject\InsuranceTypeEnum;

class InsuranceDefinition
{
    /**
     * @param InsuranceId $id
     * @param InsuranceTypeEnum $type
     * @param string $description
     * @param MoneyAmount[] $annualCost
     */
    public function __construct(
        public InsuranceId       $id,
        public InsuranceTypeEnum $type,
        public string            $description,
        public array             $annualCost,
        // TODO add field for benefits or coverage details
    )
    {
    }

    public function getAnnualCost(int $currentPhase = 1): MoneyAmount
    {
        return $this->annualCost[$currentPhase];
    }

    public function getLabelWithAnnualCost(int $currentPhase = 1): string
    {
        return sprintf('%s (%s € / Jahr)', $this->type->value, self::getAnnualCost($currentPhase)->value);
    }
}
