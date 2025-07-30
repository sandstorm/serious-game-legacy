<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\Dto;

use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\LebenszielPhaseId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

/**
 * Use this interface for all cards
 */
interface CardDefinition
{
    public function getId(): CardId;
    public function getCategory(): CategoryId;
    public function getTitle(): string;
    public function getDescription(): string;
    public function getPhase(): LebenszielPhaseId;
}
