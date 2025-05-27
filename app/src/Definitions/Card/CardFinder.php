<?php

declare(strict_types=1);

namespace Domain\Definitions\Card;

use Domain\Definitions\Card\Dto\Card;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\JobRequirements;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\Gehalt;
use Domain\Definitions\Card\ValueObject\PileId;

/**
 * TODO this is just a placeholder until we have a mechanism to organize our cards in piles (DB/files/?)
 */
readonly final class CardFinder
{
    /**
     * @param PileId $pileId
     * // TODO maybe just Card[] ?
     * @return KategorieCardDefinition[]|JobCardDefinition[]
     */
    public static function getCardsForPile(PileId $pileId): array
    {
        return match ($pileId) {
            PileId::BILDUNG_PHASE_1 => self::getCardsForBildungAndKarriere1(),
            PileId::FREIZEIT_PHASE_1 => self::getCardsForSozialesAndFreizeit1(),
            PileId::JOBS_PHASE_1 => self::getCardsForErwerbseinkommen1(),
            // TODO
            PileId::BILDUNG_PHASE_2 => [],
            PileId::FREIZEIT_PHASE_2 => [],
            PileId::ERWERBSEINKOMMEN_PHASE_2 => [],
            PileId::BILDUNG_PHASE_3 => [],
            PileId::FREIZEIT_PHASE_3 => [],
            PileId::ERWERBSEINKOMMEN_PHASE_3 => [],
        };
    }

    public static function getCardById(CardId $cardId): Card
    {
        $allCards = [
            ...self::getCardsForBildungAndKarriere1(),
            ...self::getCardsForSozialesAndFreizeit1(),
            ...self::getCardsForErwerbseinkommen1(),
            ];

        if (array_key_exists($cardId->value, $allCards)) {
            return $allCards[$cardId->value];
        }

        throw new \RuntimeException('Card ' . $cardId . ' does not exist', 1747645954);
    }

    /**
     * @return JobCardDefinition[]
     */
    public static function getJobsBasedOnPlayerResources(ResourceChanges $playerResources): array
    {
        // TODO consider the player's phase
        return array_slice(
            array_filter(self::getCardsForErwerbseinkommen1(), function ($job) use ($playerResources) {
                return $job->requirements->freizeitKompetenzsteine < $playerResources->freizeitKompetenzsteinChange &&
                    $job->requirements->bildungKompetenzsteine < $playerResources->bildungKompetenzsteinChange &&
                    $job->requirements->zeitsteine < $playerResources->zeitsteineChange;
            }),
            0,
            3
        );
    }

    /**
     * @return KategorieCardDefinition[]
     */
    private static function getCardsForBildungAndKarriere1(): array
    {
        return [
            "buk0" => new KategorieCardDefinition(
                id: new CardId('buk0'),
                pileId: PileId::BILDUNG_PHASE_1,
                title: 'Sprachkurs',
                description: 'Mache einen Sprachkurs über drei Monate im Ausland.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: -11000,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk1" => new KategorieCardDefinition(
                id: new CardId('buk1'),
                pileId: PileId::BILDUNG_PHASE_1,
                title: 'Erste-Hilfe-Kurs',
                description: 'Du machst einen Erste-Hilfe-Kurs, um im Notfall richtig zu reagieren.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: -300,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk2" => new KategorieCardDefinition(
                id: new CardId('buk2'),
                pileId: PileId::BILDUNG_PHASE_1,
                title: 'Gedächtnistraining',
                description: 'Mache jeden Tag 20 Minuten Gedächtnistraining, um dich geistig fit zu halten.',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
        ];
    }

    /**
     * @return KategorieCardDefinition[]
     */
    private static function getCardsForSozialesAndFreizeit1(): array
    {
        return [
            "suf0" => new KategorieCardDefinition(
                id: new CardId('suf0'),
                pileId: PileId::FREIZEIT_PHASE_1,
                title: 'Ehrenamtliches Engagement',
                description: 'Du engagierst dich ehrenamtlich für eine Organisation, die es Menschen mit Behinderung ermöglicht einen genialen Urlaub mit Sonne, Strand und Meer zu erleben. Du musst die Kosten dafür allerdings selbst tragen.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: -1200,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf1" => new KategorieCardDefinition(
                id: new CardId('suf1'),
                pileId: PileId::FREIZEIT_PHASE_1,
                title: 'Spende',
                description: 'Bei deinem Einkauf spendest du nun immer Tiernahrung für die umliegende Tierheime. Dein Spendebeitrag ist 200 €.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: -200,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf2" => new KategorieCardDefinition(
                id: new CardId('suf2'),
                pileId: PileId::FREIZEIT_PHASE_1,
                title: 'kostenlose Nachhilfe',
                description: 'Du gibst kostenlose Nachhilfe für sozial benachteiligte Kinder. Du verlierst einen Zeitstein.',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
        ];
    }

    /**
     * @return JobCardDefinition[]
     */
    private static function getCardsForErwerbseinkommen1(): array
    {
        return [
            "ee0" => new JobCardDefinition(
                id: new CardId('ee0'),
                pileId: PileId::JOBS_PHASE_1,
                title: 'Fachinformatikerin',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new Gehalt(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                ),
            ),
            "ee1" => new JobCardDefinition(
                id: new CardId('ee1'),
                pileId: PileId::JOBS_PHASE_1,
                title: 'Pflegefachkraft',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new Gehalt(25000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                ),
            ),
            "ee2" => new JobCardDefinition(
                id: new CardId('ee2'),
                pileId: PileId::JOBS_PHASE_1,
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
