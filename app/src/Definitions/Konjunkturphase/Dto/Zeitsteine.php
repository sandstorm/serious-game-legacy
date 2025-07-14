<?php

declare(strict_types=1);

namespace Domain\Definitions\Konjunkturphase\Dto;

class Zeitsteine implements \JsonSerializable
{
    /**
     * @param ZeitsteinePerPlayer[] $zeitsteine
     */
    public function __construct(
        public array $zeitsteine,
    ) {
    }

    /**
     * @param array<string,mixed> $in
     */
    public static function fromArray(array $in): self
    {
        return new self(
            zeitsteine: array_map(
                static fn(array $slot) => ZeitsteinePerPlayer::fromArray($slot),
                $in['zeitsteine'] ?? []
            ),
        );
    }

    public function __toString(): string
    {
        return '[Zeitsteine: ' . implode(', ', array_map(static fn(ZeitsteinePerPlayer $z) => (string)$z, $this->zeitsteine)) . ']';
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'zeitsteine' => $this->zeitsteine,
        ];
    }

    public function getAmountOfZeitsteineForPlayer(int $amountOfPlayers): int
    {
        foreach ($this->zeitsteine as $zeitsteinePerPlayer) {
            if ($zeitsteinePerPlayer->amountOfPlayers === $amountOfPlayers) {
                return $zeitsteinePerPlayer->zeitsteinePerPlayer;
            }
        }
        throw new \InvalidArgumentException('No Zeitsteine found for ' . $amountOfPlayers . ' players');
    }
}
