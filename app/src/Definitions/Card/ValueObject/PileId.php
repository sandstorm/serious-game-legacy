<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\ValueObject;

use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

final readonly class PileId implements \JsonSerializable, \Stringable
{
    public function __construct(public CategoryId $categoryId, public LebenszielPhaseId $phaseId = LebenszielPhaseId::ANY_PHASE)
    {
    }

    /**
     * @param array<string, mixed> $values
     * @return self
     */
    public static function fromArray(array $values): self
    {
        return new self(
            categoryId: CategoryId::from($values['categoryId']),
            phaseId: LebenszielPhaseId::from($values['phaseId']),
        );
    }

    public function __toString(): string
    {
        return $this->categoryId->value . '_' . $this->phaseId->value;
    }

    public static function fromString(string $input): self
    {
        [$categoryString, $phaseString] = explode('_', $input);
        return new self(CategoryId::from($categoryString), LebenszielPhaseId::from(intval($phaseString)));
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'categoryId' => $this->categoryId,
            'phaseId' => $this->phaseId,
        ];
    }

    public function equals(PileId $other): bool
    {
        return $this->categoryId->value === $other->categoryId->value && $this->phaseId->value === $other->phaseId->value;
    }
}
