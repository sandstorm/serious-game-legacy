<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Dto;


final readonly class AktionValidationResult
{
    public function __construct(
        public bool     $canExecute,
        public ?string  $reason = null,
    ) {
    }


    /**
     * @param array{canExecute: bool, reason: string} $values
     * @return self
     */
    public static function fromString(array $values): self
    {
        return new self(
            canExecute: $values['canExecute'],
            reason: $values['reason'],
        );
    }
}
