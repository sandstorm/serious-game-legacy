<?php

declare(strict_types=1);

namespace Domain\Definitions\Konjunkturphase\Dto;

class ZeitsteinePerPlayer implements \JsonSerializable
{
    public function __construct(
        public int $amountOfPlayers,
        public int $zeitsteinePerPlayer,
    ) {
    }

    /**
     * @param array<string,mixed> $in
     */
    public static function fromArray(array $in): self
    {
        return new self(
            amountOfPlayers: $in['amountOfPlayers'],
            zeitsteinePerPlayer: $in['zeitsteinePerPlayer'],
        );
    }

    public function __toString(): string
    {
        return '[ZeitsteinePerPlayer: ' . $this->amountOfPlayers . ' players, ' . $this->zeitsteinePerPlayer . ' Zeitsteine each]';
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'amountOfPlayers' => $this->amountOfPlayers,
            'zeitsteinePerPlayer' => $this->zeitsteinePerPlayer,
        ];
    }
}
