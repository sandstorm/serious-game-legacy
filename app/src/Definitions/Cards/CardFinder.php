<?php

declare(strict_types=1);

namespace Domain\Definitions\Cards;

use Domain\CoreGameLogic\Dto\ValueObject\CardId;
use Domain\CoreGameLogic\Dto\ValueObject\PileId;
use Domain\CoreGameLogic\Dto\ValueObject\CardRequirements;
use Domain\CoreGameLogic\Dto\ValueObject\ResourceChanges;
use Domain\Definitions\Pile\Enum\PileEnum;
use Domain\Definitions\Cards\Model\CardDefinition;

/**
 * TODO this is just a placeholder until we have a mechanism to organize our cards in piles (DB/files/?)
 */
readonly final class CardFinder
{
    /**
     * @param PileId $pileId
     * @return CardDefinition[]
     */
    public static function getCardsForPile(PileId $pileId): array
    {
        return match ($pileId->value) {
            PileEnum::BILDUNG_PHASE_1 => self::getCardsForBildungAndKarriere1(),
            PileEnum::FREIZEIT_PHASE_1 => self::getCardsForSozialesAndFreizeit1(),
            PileEnum::ERWERBSEINKOMMEN_PHASE_1 => self::getCardsForErwerbseinkommen1(),
            default => throw new \RuntimeException('Pile ' . $pileId . ' does not exist')
        };
    }

    public static function getCardById(CardId $cardId): CardDefinition
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
     * @return CardDefinition[]
     */
    private static function getCardsForBildungAndKarriere1(): array
    {
        return [
            "buk0" => new CardDefinition(
                id: new CardId('buk0'),
                pileId: new PileId(PileEnum::BILDUNG_PHASE_1),
                kurzversion: 'Sprachkurs',
                langversion: 'Mache einen Sprachkurs über drei Monate im Ausland.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: -11000,
                    bildungKompetenzsteinChange: +1,
                ),
                requirements: new CardRequirements(
                    guthaben: 11000,
                ),
            ),
            "buk1" => new CardDefinition(
                id: new CardId('buk1'),
                pileId: new PileId(PileEnum::BILDUNG_PHASE_1),
                kurzversion: 'Erste-Hilfe-Kurs',
                langversion: 'Du machst einen Erste-Hilfe-Kurs, um im Notfall richtig zu reagieren.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: -300,
                    bildungKompetenzsteinChange: +1,
                ),
                requirements: new CardRequirements(
                    guthaben: 300,
                ),
            ),
            "buk2" => new CardDefinition(
                id: new CardId('buk2'),
                pileId: new PileId(PileEnum::BILDUNG_PHASE_1),
                kurzversion: 'Gedächtnistraining',
                langversion: 'Mache jeden Tag 20 Minuten Gedächtnistraining, um dich geistig fit zu halten.',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
                requirements: new CardRequirements(
                    zeitsteine: 1,
                ),
            ),
        ];
    }

    /**
     * @return CardDefinition[]
     */
    private static function getCardsForSozialesAndFreizeit1(): array
    {
        return [
            "suf0" => new CardDefinition(
                id: new CardId('suf0'),
                pileId: new PileId(PileEnum::FREIZEIT_PHASE_1),
                kurzversion: 'Ehrenamtliches Engagement',
                langversion: 'Du engagierst dich ehrenamtlich für eine Organisation, die es Menschen mit Behinderung ermöglicht einen genialen Urlaub mit Sonne, Strand und Meer zu erleben. Du musst die Kosten dafür allerdings selbst tragen.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: -1200,
                    freizeitKompetenzsteinChange: +1,
                ),
                requirements: new CardRequirements(
                    guthaben: 1200,
                ),
            ),
            "suf1" => new CardDefinition(
                id: new CardId('suf1'),
                pileId: new PileId(PileEnum::FREIZEIT_PHASE_1),
                kurzversion: 'Spende',
                langversion: 'Bei deinem Einkauf spendest du nun immer Tiernahrung für die umliegende Tierheime. Dein Spendebeitrag ist 200 €.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: -200,
                    freizeitKompetenzsteinChange: +1,
                ),
                requirements: new CardRequirements(
                    guthaben: 200,
                ),
            ),
            "suf2" => new CardDefinition(
                id: new CardId('suf2'),
                pileId: new PileId(PileEnum::FREIZEIT_PHASE_1),
                kurzversion: 'kostenlose Nachhilfe',
                langversion: 'Du gibst kostenlose Nachhilfe für sozial benachteiligte Kinder. Du verlierst einen Zeitstein.',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
                requirements: new CardRequirements(
                    zeitsteine: 1,
                ),
            ),
        ];
    }

    /**
     * @return CardDefinition[]
     */
    private static function getCardsForErwerbseinkommen1(): array
    {
        return [
            "ee0" => new CardDefinition(
                id: new CardId('ee0'),
                pileId: new PileId(PileEnum::ERWERBSEINKOMMEN_PHASE_1),
                kurzversion: 'Fachinformatikerin',
                langversion: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1, // TODO p.a. -> not yet implemented
                    newErwerbseinkommen: 34000,
                ),
                requirements: new CardRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                ),
            ),
            "ee1" => new CardDefinition(
                id: new CardId('ee1'),
                pileId: new PileId(PileEnum::ERWERBSEINKOMMEN_PHASE_1),
                kurzversion: 'Pflegefachkraft',
                langversion: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1, // TODO p.a. -> not yet implemented
                    newErwerbseinkommen: 25000,
                ),
                requirements: new CardRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                ),
            ),
            "ee2" => new CardDefinition(
                id: new CardId('ee2'),
                pileId: new PileId(PileEnum::ERWERBSEINKOMMEN_PHASE_1),
                kurzversion: 'Taxifahrer:in',
                langversion: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1, // TODO p.a. -> not yet implemented
                    newErwerbseinkommen: 18000,
                ),
                requirements: new CardRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 1,
                ),
            ),
        ];
    }

}
