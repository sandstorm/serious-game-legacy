<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

readonly class Szenario implements \JsonSerializable
{
    /**
     * @param string $value
     * @param string $description
     * @param Kategorie[] $categories
     */
    public function __construct(
        public string $value,
        public string $description,
        public array $categories
    ) {
    }

    public function __toString(): string
    {
        return '[Szenario: '.$this->value.']';
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'value' => $this->value,
            'description' => $this->description,
            'categories' => array_map(
                static fn (Kategorie $category) => $category->jsonSerialize(),
                $this->categories
            ),
        ];
    }
}
