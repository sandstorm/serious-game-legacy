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
        public ?float $modifyLebenshaltungskostenMultiplier = null,
        public ?MoneyAmount $modifyLebenshaltungskostenMinValue = null,
    ) {
    }

    /**
     * @param array{
     *      numberOfTurns: int,
     *      modifyGehaltPercent: int,
     *      modifySteuernUndAbgabenPercent: int,
     *      modifyKostenBildungUndKarrierePercent: int,
     *      modifyKostenSozialesUndFreizeitPercent: int,
     *      modifyLebenshaltungskostenMultiplier: float,
     *      modifyLebenshaltungskostenMinValue: MoneyAmount,
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
            modifyLebenshaltungskostenMultiplier: $values['modifyLebenshaltungskostenMultiplier'],
            modifyLebenshaltungskostenMinValue: $values['modifyLebenshaltungskostenMinValue'],
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
            'modifyLebenshaltungskostenMultiplier' => $this->modifyLebenshaltungskostenMultiplier,
            'modifyLebenshaltungskostenMinValue' => $this->modifyLebenshaltungskostenMinValue,
        ];
    }
}
