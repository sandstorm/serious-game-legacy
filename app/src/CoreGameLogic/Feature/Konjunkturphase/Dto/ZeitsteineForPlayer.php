<?php
declare(strict_types=1);
namespace Domain\CoreGameLogic\Feature\Konjunkturphase\Dto;

use Domain\CoreGameLogic\PlayerId;

/**
 * This is used to track which player gets which number of Zeitsteine
 */
readonly final class ZeitsteineForPlayer implements \JsonSerializable
{
    public function __construct(public PlayerId $playerId, public int $zeitsteine)
    {
    }

    /**
     * @param array<mixed> $values
     * @return self
     */
    public static function fromArray(array $values): self
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            zeitsteine: $values['zeitsteine'],
        );
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId->value,
            'zeitsteine' => $this->zeitsteine,
        ];
    }
}
