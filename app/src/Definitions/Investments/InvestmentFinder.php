<?php

declare(strict_types=1);

namespace Domain\Definitions\Investments;

use Domain\Definitions\Investments\Dto\InvestmentDefinition;
use Domain\Definitions\Investments\ValueObject\InvestmentId;
use RuntimeException;

class InvestmentFinder
{
    /**
     * @return InvestmentDefinition[]
     */
    public static function getAllInvestments(): array
    {
        $merfedesPenz = new InvestmentDefinition(
            id: InvestmentId::MERFEDES_PENZ,
            description: "Ein traditionsreicher Automobilkonzern mit stabilen Umsätzen und zuverlässigen Dividenden. Ideal geeignet als langfristiges Basisinvestment.",
            longTermTrend: 7,
            fluctuations: 15,
            jumpPerYear: 0,
            jumpSize: -0.5,
            jumpControl: 0,
        );
        $betaPear = new InvestmentDefinition(
            id: InvestmentId::BETA_PEAR,
            description: "Ein junges, ambitioniertes Tech-Unternehmen mit Fokus auf Nachhaltigkeit, das auf die nächste große Innovation setzt. Die Aktie bietet hohe, aber stark schwankende Kurschancen und zahlt keine Dividenden.",
            longTermTrend: 9,
            fluctuations: 40,
            jumpPerYear: 0.8,
            jumpSize: 0,
            jumpControl: 2,
        );
        $etfMcsiWorld = new InvestmentDefinition(
            id: InvestmentId::ETF_MSCI_WORLD,
            description: "Ein weltweiter Indexfonds, der über 1500 Blue-Chip-Aktien bündelt und somit eine breite Diversifikation ermöglicht und Einzelrisiken reduziert.",
            longTermTrend: 7,
            fluctuations: 15,
            jumpPerYear: 0,
            jumpSize: 0,
            jumpControl: 0,
        );
        $etfCleanEnergy = new InvestmentDefinition(
            id: InvestmentId::ETF_CLEAN_ENERGY,
            description: "Ein Themen-ETF für erneuerbare Energien, der vom Green-Tech-Boom profitiert, jedoch deutlich stärker schwankt als der Gesamtmarkt.",
            longTermTrend: 10,
            fluctuations: 27,
            jumpPerYear: 0,
            jumpSize: 0,
            jumpControl: 0,
        );
        $batCoin = new InvestmentDefinition(
            id: InvestmentId::BAT_COIN,
            description: "Eine etablierte Kryptowährung mit begrenztem Angebot, die oft als digitales Gold bezeichnet wird. Obwohl langfristig im Aufwärtstrend, sind starke tägliche Schwankungen üblich.",
            longTermTrend: 12,
            fluctuations: 60,
            jumpPerYear: 0,
            jumpSize: 0,
            jumpControl: 0,
        );
        $memeCoin = new InvestmentDefinition(
            id: InvestmentId::MEME_COIN,
            description: "Eine hochspekulative Kryptowährung, deren Kurs stark von Social-Media-Hypes beeinflusst wird. Sie kann sich in kurzer Zeit vervielfachen, aber genauso schnell nahezu wertlos werden.",
            longTermTrend: 20,
            fluctuations: 100,
            jumpPerYear: 4,
            jumpSize: -1,
            jumpControl: 12,
        );

        return [
            $merfedesPenz,
            $betaPear,
            $etfMcsiWorld,
            $etfCleanEnergy,
            $batCoin,
            $memeCoin,
        ];
    }

    /**
     * @param InvestmentId $id
     * @return InvestmentDefinition
     */
    public static function findInvestmentById(InvestmentId $id): InvestmentDefinition
    {
        $investments = self::getAllInvestments();
        foreach ($investments as $investment) {
            if ($investment->id === $id) {
                return $investment;
            }
        }

        throw new RuntimeException('Investment ' . $id->value . ' not found', 1755089107);
    }

}
