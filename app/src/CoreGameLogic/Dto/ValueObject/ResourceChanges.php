<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

readonly class ResourceChanges implements \JsonSerializable
{
    public function __construct(
        public int $guthabenChange,
        public int $zeitsteineChange
    )
    {
    }

    /**
     * @param array<string,mixed> $values
     */
    public static function fromArray(array $values): self
    {
        return new self(
            guthabenChange: $values['guthabenChange'],
            zeitsteineChange:  $values['zeitsteineChange']
        );
    }

    public function __toString(): string
    {
        return '[guthabenChange: '.$this->guthabenChange.' zeitsteineChange: '.$this->zeitsteineChange.']';
    }

    public function accumulate(self $change): self
    {
        return new self(
            guthabenChange: $this->guthabenChange + $change->guthabenChange,
            zeitsteineChange: $this->zeitsteineChange + $change->zeitsteineChange
        );
    }

    public function jsonSerialize(): mixed
    {
        return [
            'guthabenChange' => $this->guthabenChange,
            'zeitsteineChange' => $this->zeitsteineChange,
        ];
    }
}
