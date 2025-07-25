<?php
declare(strict_types=1);

namespace Domain\Definitions\Card\Dto;

use Domain\Definitions\Card\ValueObject\AnswerId;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\PileId;
use Random\Randomizer;
use RuntimeException;

class WeiterbildungCardDefinition implements CardDefinition
{

    /**
     * @param CardId $id
     * @param PileId $pileId
     * @param string $title
     * @param string $description
     * @param AnswerOption[] $answerOptions
     */
    public function __construct(
        public CardId           $id,
        public PileId           $pileId,
        public string           $title,
        public string           $description,
        public array            $answerOptions,
    ) {
    }

    public function getId(): CardId
    {
        return $this->id;
    }

    public function getPileId(): PileId
    {
        return $this->pileId;
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

//    public function getAnswerOptionsShuffled(WeiterbildungCardDefinition $weiterbildungCardDefinition): array
//    {
//        $randomizer = new Randomizer();
//        return $randomizer->shuffleArray($weiterbildungCardDefinition->answerOptions);
//    }
}
