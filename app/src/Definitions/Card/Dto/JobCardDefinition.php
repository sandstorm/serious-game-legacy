<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\Dto;

use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\Gehalt;
use Domain\Definitions\Card\ValueObject\PileId;

final readonly class JobCardDefinition implements CardDefinition
{
    public function __construct(
        public CardId           $id,
        public PileId           $pileId,
        public string           $title,
        public string           $description,
        public Gehalt           $gehalt,
        public JobRequirements  $requirements,
    ) {
    }

    /**
     * @param array{
     *     id: string,
     *     pileId: string,
     *     title: string,
     *     description: string,
     *     gehalt: int,
     *     requirements: array{
     *          zeitsteine: int,
     *          bildungKompetenzsteine: int,
     *          freizeitKompetenzsteine: int
     *     }
     * } $job
     * @return self
     */
    public static function fromString(array $job): self
    {
        return new self(
            id: new CardId($job['id']),
            pileId: PileId::from($job['pileId']),
            title: $job['title'],
            description: $job['description'],
            gehalt: new Gehalt($job['gehalt']),
            requirements: JobRequirements::fromString($job['requirements']),
        );
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
}
