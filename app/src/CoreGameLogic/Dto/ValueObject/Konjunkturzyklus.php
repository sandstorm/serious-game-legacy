<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

use Domain\CoreGameLogic\Dto\Enum\KonjunkturzyklusType;

readonly class Konjunkturzyklus implements \JsonSerializable
{
    /**
     * @param KonjunkturzyklusType $type
     * @param string $description
     * @param Kategorie[] $categories
     */
    public function __construct(
        public KonjunkturzyklusType $type,
        public string               $description,
        public array                $categories
    )
    {
    }

    /**
     * @param array<string,mixed> $in
     */
    public static function fromArray(array $in): self {
        return new self(
            type: KonjunkturzyklusType::fromString($in['type']),
            description: $in['description'],
            categories: array_map(
                static fn(array $category) => Kategorie::fromArray($category),
                $in['categories']
            )
        );
    }

    public function __toString(): string
    {
        return '[Konjunkturzyklus: ' . $this->type->value . ']';
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type,
            'description' => $this->description,
            'categories' => $this->categories
        ];
    }
}
