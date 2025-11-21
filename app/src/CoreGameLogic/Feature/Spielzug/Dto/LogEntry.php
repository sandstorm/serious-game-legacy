<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Dto;

use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;

final readonly class LogEntry
{
    public function __construct(
        private string $text,
        private ?PlayerId $playerId = null,
        private ?ResourceChanges $resourceChanges = null,
    ) {}


    /**
     * @param array<string, mixed> $values
     * @return self
     */
    public static function fromArray(array $values): self
    {
        return new self(
            text: $values['text'],
            playerId: PlayerId::fromString($values['playerId']),
            resourceChanges: ResourceChanges::fromArray($values['resourceChanges'])
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId,
            'text' => $this->text,
            'resourceChanges' => $this->resourceChanges,
        ];
    }

    public function getPlayerId(): PlayerId|null
    {
        return $this->playerId;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getResourceChanges(): ResourceChanges|null
    {
        return $this->resourceChanges;
    }
}
