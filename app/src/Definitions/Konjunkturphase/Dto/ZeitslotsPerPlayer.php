<?php

declare(strict_types=1);

namespace Domain\Definitions\Konjunkturphase\Dto;

class ZeitslotsPerPlayer implements \JsonSerializable
{
    public function __construct(
        public int $amountOfPlayers,
        public int $zeitslotsPerPlayer,
    ) {
    }

    /**
     * @param array<string,mixed> $in
     */
    public static function fromArray(array $in): self
    {
        return new self(
            amountOfPlayers: $in['amountOfPlayers'],
            zeitslotsPerPlayer: $in['zeitslotsPerPlayer'],
        );
    }

    public function __toString(): string
    {
        return '[ZeitslotsPerPlayer: ' . $this->amountOfPlayers . ' players, ' . $this->zeitslotsPerPlayer . ' Zeitslots each]';
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'amountOfPlayers' => $this->amountOfPlayers,
            'zeitslotsPerPlayer' => $this->zeitslotsPerPlayer,
        ];
    }
}
