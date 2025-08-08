<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\ImmobilienPrice;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\InvestmentPrice;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior\ProvidesImmobilienPriceChanges;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior\ProvidesInvestmentPriceChanges;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesModifiers;
use Domain\CoreGameLogic\Feature\Spielzug\Modifier\ModifierBuilder;
use Domain\CoreGameLogic\Feature\Spielzug\Modifier\ModifierCollection;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseFinder;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphasenId;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphaseTypeEnum;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;

final readonly class KonjunkturphaseWasChanged implements GameEventInterface, ProvidesInvestmentPriceChanges, ProvidesModifiers, ProvidesImmobilienPriceChanges
{
    /**
     * @param InvestmentPrice[] $investmentPrices
     * @param ImmobilienPrice[] $immobilienPrices
     */
    public function __construct(
        public KonjunkturphasenId $id,
        public Year $year,
        public KonjunkturphaseTypeEnum $type,
        public array $investmentPrices,
        public array $immobilienPrices
    ) {
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
            immobilienPrices: array_map(
                static fn($immobilienPrice) => ImmobilienPrice::fromArray($immobilienPrice),
                $values['immobilienPrices']
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
            'immobilienPrices' => $this->immobilienPrices,
        ];
    }

    /**
     * @return InvestmentPrice[]
     */
    public function getInvestmentPrices(): array
    {
        return $this->investmentPrices;
    }

    /**
     * This does not check for player id, since Modifiers from the Konjunkturphase are always enabled for all
     * players until the end of the Konjunkturphase
     * @param PlayerId|null $playerId
     * @return ModifierCollection
     */
    public function getModifiers(?PlayerId $playerId = null): ModifierCollection
    {
        $modifiers = [];
        $konjunkturphaseDefinition = KonjunkturphaseFinder::findKonjunkturphaseById($this->id);
        foreach ($konjunkturphaseDefinition->getModifierIds() as $modifierId) {
            $modifiers = [
                ...$modifiers,
                ...ModifierBuilder::build(
                    modifierId: $modifierId,
                    playerId: $playerId,
                    playerTurn: new PlayerTurn(0), // TODO make PlayerTurn optional
                    year: $this->year,
                    modifierParameters: $konjunkturphaseDefinition->getModifierParameters(),
                    description: $konjunkturphaseDefinition->description,
                ),
            ];
        }
        return new ModifierCollection($modifiers);
    }

    /**
     * @return ImmobilienPrice[]
     */
    public function getImmobilienPrices(): array
    {
        return $this->immobilienPrices;
    }
}
