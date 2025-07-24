<?php
declare(strict_types=1);

namespace Domain\Definitions\Card\Dto;

use Domain\Definitions\Card\ValueObject\AnswerId;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\LebenszielPhaseId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use RuntimeException;

class WeiterbildungCardDefinition implements CardDefinition
{

    /**
     * @param CardId $id
     * @param string $title
     * @param string $description
     * @param AnswerOption[] $answerOptions
     */
    public function __construct(
        protected CardId     $id,
        protected string     $title,
        protected string     $description,
        protected array      $answerOptions,
    )
    {
    }

    public function getId(): CardId
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function getCorrectAnswerId(): AnswerId
    {
        foreach ($this->answerOptions as $answerOption) {
            if ($answerOption->isCorrect) {
                return $answerOption->id;
            }
        }
        throw new RuntimeException('No correct answer found', 1753189730);
    }

    public function getCategory(): CategoryId
    {
        return CategoryId::WEITERBILDUNG;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPhase(): LebenszielPhaseId
    {
        return LebenszielPhaseId::ANY_PHASE;
    }

    /**
     * @return AnswerOption[]
     */
    public function getAnswerOptions(): array
    {
        return $this->answerOptions;
    }
}
