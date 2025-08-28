<?php

declare(strict_types=1);

namespace Domain\Definitions\Konjunkturphase\Dto;

use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\EreignisPrerequisitesId;

/**
 * Represents the model of the ResourceChanges that may occur when the player meets a certain requirement.
 *
 * There are 3 special cases for KonjunkturphaseChange that will be handled in {@see StartKonjunkturphaseForPlayerAktion}:
 *
 * **Lohnsonderzahlung**
 *
 * If the `$lohnsonderzahlungPercent` is specified, the player will get the specified percent amount of their current
 * Gehalt as a bonus payment
 *
 * **Grundsteuer**
 *
 * If `$isGrundsteuer` is `true`, the Player will need to pay the guthabenChange specified in `$resourceChanges` for
 * each property they own.
 *
 * **Extrazins**
 *
 * If `$isExtraZins` is `true`, the Player will pay the guthabenChange specified in `$resourceChanges` for each loan
 * they have.
 */
class ConditionalResourceChange
{
    /**
     * @param EreignisPrerequisitesId $prerequisite
     * @param ResourceChanges $resourceChanges
     * @param float|null $lohnsonderzahlungPercent
     * @param bool $isGrundsteuer
     * @param bool $isExtraZins
     */
    public function __construct(
        public EreignisPrerequisitesId $prerequisite,
        public ResourceChanges $resourceChanges,
        // Optional special cases for some KonjunkturphaseChanges
        public ?float $lohnsonderzahlungPercent = null,
        public bool $isGrundsteuer = false,
        public bool $isExtraZins = false,
    ) {
    }
}
