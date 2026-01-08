<?php
declare(strict_types=1);

namespace Domain\Definitions\Card\Dto;


use Domain\Definitions\Card\ValueObject\MoneyAmount;

readonly final class ModifierParameters implements \JsonSerializable
{
    public function __construct(
        public ?int $numberOfTurns = null,
        public ?int $modifyGehaltPercent = null,
        public ?int $modifySteuernUndAbgabenPercent = null,
        public ?int $modifyKostenBildungUndKarrierePercent = null,
        public ?int $modifyKostenSozialesUndFreizeitPercent = null,
        public float|int|null $modifyAdditionalLebenshaltungskostenPercentage = null,
        public ?MoneyAmount $modifyLebenshaltungskostenMinValue = null,
        public float|int|null $modifyLebenshaltungskostenMultiplier = null,
    ) {
    }

    /**
     * @param array{
     *      numberOfTurns: int,
     *      modifyGehaltPercent: int,
     *      modifySteuernUndAbgabenPercent: int,
     *      modifyKostenBildungUndKarrierePercent: int,
     *      modifyKostenSozialesUndFreizeitPercent: int,
     *      modifyAdditionalLebenshaltungskostenPercentage: float,
     *      modifyLebenshaltungskostenMinValue: MoneyAmount,
     *      modifyLebenshaltungskostenMultiplier: float,
     *     } $values
     * @return self
     */
    public static function fromArray(array $values): self
    {
        return new self(
            numberOfTurns: $values['numberOfTurns'],
            modifyGehaltPercent: $values['modifyGehaltPercent'],
            modifySteuernUndAbgabenPercent: $values['modifySteuernUndAbgabenPercent'],
            modifyKostenBildungUndKarrierePercent: $values['modifyKostenBildungUndKarrierePercent'],
            modifyKostenSozialesUndFreizeitPercent: $values['modifyKostenSozialesUndFreizeitPercent'],
            modifyAdditionalLebenshaltungskostenPercentage: $values['modifyAdditionalLebenshaltungskostenPercentage'],
            modifyLebenshaltungskostenMinValue: $values['modifyLebenshaltungskostenMinValue'],
            modifyLebenshaltungskostenMultiplier: $values['modifyLebenshaltungskostenMultiplier'],
        );
    }

    public function jsonSerialize(): mixed
    {
        return [
            'numberOfTurns' => $this->numberOfTurns,
            'modifyGehaltPercent' => $this->modifyGehaltPercent,
            'modifySteuernUndAbgabenPercent' => $this->modifySteuernUndAbgabenPercent,
            'modifyKostenBildungUndKarrierePercent' => $this->modifyKostenBildungUndKarrierePercent,
            'modifyKostenSozialesUndFreizeitPercent' => $this->modifyKostenSozialesUndFreizeitPercent,
            'modifyAdditionalLebenshaltungskostenPercentage' => $this->modifyAdditionalLebenshaltungskostenPercentage,
            'modifyLebenshaltungskostenMinValue' => $this->modifyLebenshaltungskostenMinValue,
            'modifyLebenshaltungskostenMultiplier' => $this->modifyLebenshaltungskostenMultiplier,
        ];
    }
}
