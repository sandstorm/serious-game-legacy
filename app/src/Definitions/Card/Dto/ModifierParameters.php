<?php
declare(strict_types=1);

namespace Domain\Definitions\Card\Dto;


readonly final class ModifierParameters implements \JsonSerializable
{
    public function __construct(
        public ?int $numberOfTurns = null,
        public ?int $modifyGehaltPercent = null,
        public ?int $modifySteuernUndAbgabenPercent = null,
        public ?int $modifyLebenshaltungskostenPercent = null,
        public ?int $modifyKostenBildungUndKarrierePercent = null,
        public ?int $modifyKostenSozialesUndFreizeitPercent = null,
    ) {
    }

    /**
     * @param array{
     *      numberOfTurns: int,
     *      modifyGehaltPercent: int,
     *      modifySteuernUndAbgabenPercent: int,
     *      modifyLebenshaltungskostenPercent: int,
     *      modifyKostenBildungUndKarrierePercent: int,
     *      modifyKostenSozialesUndFreizeitPercent: int,
     *     } $values
     * @return self
     */
    public static function fromArray(array $values): self
    {
        return new self(
            numberOfTurns: $values['numberOfTurns'],
            modifyGehaltPercent: $values['modifyGehaltPercent'],
            modifySteuernUndAbgabenPercent: $values['modifySteuernUndAbgabenPercent'],
            modifyLebenshaltungskostenPercent: $values['modifyLebenshaltungskostenPercent'],
            modifyKostenBildungUndKarrierePercent: $values['modifyKostenBildungUndKarrierePercent'],
            modifyKostenSozialesUndFreizeitPercent: $values['modifyKostenSozialesUndFreizeitPercent'],
        );
    }

    public function jsonSerialize(): mixed
    {
        return [
            'numberOfTurns' => $this->numberOfTurns,
            'modifyGehaltPercent' => $this->modifyGehaltPercent,
            'modifySteuernUndAbgabenPercent' => $this->modifySteuernUndAbgabenPercent,
            'modifyLebenshaltungskostenPercent' => $this->modifyLebenshaltungskostenPercent,
            'modifyKostenBildungUndKarrierePercent' => $this->modifyKostenBildungUndKarrierePercent,
            'modifyKostenSozialesUndFreizeitPercent' => $this->modifyKostenSozialesUndFreizeitPercent,
        ];
    }
}
