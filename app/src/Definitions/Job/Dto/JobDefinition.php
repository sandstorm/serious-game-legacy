<?php

declare(strict_types=1);

namespace Domain\Definitions\Job\Dto;

use Domain\Definitions\Job\ValueObject\Gehalt;
use Domain\Definitions\Job\ValueObject\JobId;

final readonly class JobDefinition
{
    public function __construct(
        public JobId            $id,
        public string           $title,
        public string           $description,
        public Gehalt           $gehalt,
        public JobRequirements  $requirements,
    ) {
    }

    /**
     * @param array{
     *     id: string,
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
            id: new JobId($job['id']),
            title: $job['title'],
            description: $job['description'],
            gehalt: new Gehalt($job['gehalt']),
            requirements: JobRequirements::fromString($job['requirements']),
        );
    }
}
