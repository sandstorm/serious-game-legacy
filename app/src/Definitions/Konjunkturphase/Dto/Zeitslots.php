<?php

declare(strict_types=1);

namespace Domain\Definitions\Konjunkturphase\Dto;

class Zeitslots implements \JsonSerializable
{
    /**
     * @param ZeitslotsPerPlayer[] $zeitslotsPerPlayers
     */
    public function __construct(
        public array $zeitslotsPerPlayers,
    ) {
    }

    /**
     * @param array<string,mixed> $in
     */
    public static function fromArray(array $in): self
    {
        return new self(
            zeitslotsPerPlayers: array_map(
                static fn(array $slot) => ZeitslotsPerPlayer::fromArray($slot),
                $in['zeitslotsPerPlayers'] ?? []
            ),
        );
    }

    public function __toString(): string
    {
        return '[Zeitslots: ' . implode(', ', array_map(static fn(ZeitslotsPerPlayer $z) => (string)$z, $this->zeitslotsPerPlayers)) . ']';
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'zeitslotsPerPlayers' => $this->zeitslotsPerPlayers,
        ];
    }

    public function getAmountOfZeitslotsForPlayer(int $amountOfPlayers): int
    {
        foreach ($this->zeitslotsPerPlayers as $zeitsteinePerPlayer) {
            if ($zeitsteinePerPlayer->amountOfPlayers === $amountOfPlayers) {
                return $zeitsteinePerPlayer->zeitslotsPerPlayer;
            }
        }
        throw new \InvalidArgumentException('No Zeitslots found for ' . $amountOfPlayers . ' players');
    }
}
