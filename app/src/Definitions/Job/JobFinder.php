<?php

declare(strict_types=1);

namespace Domain\Definitions\Job;

use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Job\Dto\JobDefinition;
use Domain\Definitions\Job\Dto\JobRequirements;
use Domain\Definitions\Job\ValueObject\Gehalt;
use Domain\Definitions\Job\ValueObject\JobId;

/**
 * TODO this is just a placeholder until we have a mechanism to organize our jobs (DB/files/?)
 */
readonly final class JobFinder
{
    /**
     * @return JobDefinition[]
     */
    public static function getSelectionBasedOnResources(ResourceChanges $playerResources): array
    {
        return array_slice(
            array_filter(self::getAllJobs(), function ($job) use ($playerResources) {
                return $job->requirements->freizeitKompetenzsteine < $playerResources->freizeitKompetenzsteinChange &&
                    $job->requirements->bildungKompetenzsteine < $playerResources->bildungKompetenzsteinChange &&
                    $job->requirements->zeitsteine < $playerResources->zeitsteineChange;
            }),
            0,
            3
            );
    }

    public static function getJobById(JobId $jobId): JobDefinition
    {
        $allCards = self::getAllJobs();

        if (array_key_exists($jobId->value, $allCards)) {
            return $allCards[$jobId->value];
        }

        throw new \RuntimeException('Card ' . $jobId . ' does not exist', 1747645954);
    }


    /**
     * @return JobDefinition[]
     */
    public static function getAllJobs(): array
    {
        return [
            "ee0" => new JobDefinition(
                id: new JobId('ee0'),
                title: 'Fachinformatikerin',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new Gehalt(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                ),
            ),
            "ee1" => new JobDefinition(
                id: new JobId('ee1'),
                title: 'Pflegefachkraft',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new Gehalt(25000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                ),
            ),
            "ee2" => new JobDefinition(
                id: new JobId('ee2'),
                title: 'Taxifahrer:in',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new Gehalt(18000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 1,
                ),
            ),
        ];
    }

}
