<?php
declare(strict_types=1);

namespace Domain\Definitions\Card\Dto;

use Domain\Definitions\Card\ValueObject\AnswerId;

class AnswerOption
{
    public function __construct(
        public AnswerId $id,
        public string   $text,
        public bool     $isCorrect = false,
    ) {
    }
}
