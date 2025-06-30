<?php

declare(strict_types=1);

namespace Domain\Definitions\Insurance;

use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Insurance\ValueObject\InsuranceId;
use Domain\Definitions\Insurance\ValueObject\InsuranceTypeEnum;

class InsuranceFinder
{
    /**
     * @var InsuranceDefinition[]
     */
    private array $insurances;

    private static ?self $instance = null;

    /**
     * @param InsuranceDefinition[] $insurances
     */
    private function __construct(array $insurances)
    {
        $this->insurances = $insurances;
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            return self::initialize();
        }
        return self::$instance;
    }

    public static function initializeForTesting(): void
    {
        self::initialize();
    }

    /**
     * @param InsuranceDefinition[] $insurances
     * @return void
     */
    public function overrideInsurancesForTesting(array $insurances): void
    {
        self::getInstance()->insurances = $insurances;
    }

    private static function initialize(): self
    {
        self::$instance = new self([
            new InsuranceDefinition(
                id: InsuranceId::create(1),
                type: InsuranceTypeEnum::HAFTPFLICHT,
                description: 'Haftpflichtversicherung',
                annualCost: [
                    1 => new MoneyAmount(100),
                    2 => new MoneyAmount(120),
                    3 => new MoneyAmount(140),
                ]
            ),
            new InsuranceDefinition(
                id: InsuranceId::create(2),
                type: InsuranceTypeEnum::UNFALLVERSICHERUNG,
                description: 'Unfallversicherung',
                annualCost: [
                    1 => new MoneyAmount(150),
                    2 => new MoneyAmount(180),
                    3 => new MoneyAmount(200),
                ]
            ),
            new InsuranceDefinition(
                id: InsuranceId::create(3),
                type: InsuranceTypeEnum::BERUFSUNFAEHIGKEITSVERSICHERUNG,
                description: 'Berufsunfähigkeitsversicherung',
                annualCost: [
                    1 => new MoneyAmount(500),
                    2 => new MoneyAmount(600),
                    3 => new MoneyAmount(700),
                ]
            ),
        ]);
        return self::$instance;
    }

    /**
     * Returns a list of all available insurances.
     *
     * @return InsuranceDefinition[]
     */
    public function getAllInsurances(): array {
        return $this->insurances;
    }

    /**
     * @param InsuranceTypeEnum $type
     * @return InsuranceDefinition
     */
    public function findInsuranceByType(InsuranceTypeEnum $type): InsuranceDefinition {
        foreach ($this->insurances as $insurance) {
            if ($insurance->type === $type) {
                return $insurance;
            }
        }

        throw new \InvalidArgumentException('Insurance not found');
    }

}
