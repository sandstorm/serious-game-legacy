<?php

declare(strict_types=1);

namespace Domain\Definitions\Insurance;

use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Insurance\ValueObject\InsuranceId;
use Domain\Definitions\Insurance\ValueObject\InsuranceTypeEnum;

class InsuranceFinder
{
    /**
     * Returns a list of all available insurances.
     *
     * @return InsuranceDefinition[]
     */
    public static function getAllInsurances(): array {
        $haftpflicht = new InsuranceDefinition(
            id: InsuranceId::create(1),
            type: InsuranceTypeEnum::HAFTPFLICHT,
            description: 'Haftpflichtversicherung',
            annualCost: new MoneyAmount(100)
        );

        $unfallversicherung = new InsuranceDefinition(
            id: InsuranceId::create(2),
            type: InsuranceTypeEnum::UNFALLVERSICHERUNG,
            description: 'Unfallversicherung',
            annualCost: new MoneyAmount(150)
        );

        $berufsunfaehigkeitsversicherung = new InsuranceDefinition(
            id: InsuranceId::create(3),
            type: InsuranceTypeEnum::BERUFSUNFAEHIGKEITSVERSICHERUNG,
            description: 'Berufsunfähigkeitsversicherung',
            annualCost: new MoneyAmount(500)
        );

        return [
            $haftpflicht,
            $unfallversicherung,
            $berufsunfaehigkeitsversicherung,
        ];
    }

}
