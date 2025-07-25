<?php
declare(strict_types=1);

namespace Domain\Definitions\Card\Dto;

use Domain\Definitions\Card\ValueObject\AnswerId;
use JsonSerializable;

class AnswerOption implements JsonSerializable
{
    public function __construct(
        public AnswerId $id,
        public string   $text,
        public bool     $isCorrect = false,
    ) {
    }

    /**
     * @param array<string,mixed> $value
     * @return self
     */
    public static function fromArray(array $value): self
    {
        return new self(
            id: AnswerId::fromString($value['id']),
            text: $value['text'],
            isCorrect: (bool) $value['isCorrect'],
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'text' => $this->text,
            'isCorrect' => $this->isCorrect,
        ];
    }
}
