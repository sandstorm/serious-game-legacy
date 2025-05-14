<?php

declare(strict_types=1);

namespace Domain\Definitions\Cards\Model;

use Domain\CoreGameLogic\Dto\ValueObject\CardId;
use Domain\CoreGameLogic\Dto\ValueObject\ResourceChanges;

class CardDefinition
{
    public function __construct(
        public CardId $id,
        public ResourceChanges $resourceChanges,
    )
    {

    }

    /**
     * @param array{id: string, resourceChanges: mixed} $values
     * @return self
     */
    public static function fromString(array $values): self
    {
        return new self(
            id: new CardId($values['id']),
            resourceChanges: ResourceChanges::fromArray($values['resourceChanges']),
        );
    }

    /**
     * @return array{id: string, resourceChanges: string}
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id->jsonSerialize(),
            'resourceChanges' => $this->resourceChanges->jsonSerialize(),
        ];
    }
}
