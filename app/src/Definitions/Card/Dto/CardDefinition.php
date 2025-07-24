<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\Dto;

use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

/**
 * Use this interface for all cards
 */
interface CardDefinition
{
    public function getId(): CardId;
    public function getPileId(): PileId; // TODO remove this?
    public function getTitle(): string;
    public function getDescription(): string;
    public function getCategory(): CategoryId;
}
