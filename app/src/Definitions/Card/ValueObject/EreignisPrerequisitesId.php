<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\ValueObject;

enum EreignisPrerequisitesId: string
{
    case HAS_JOB = 'Spieler hat einen Job';
    case HAS_CHILD = 'Spieler hat ein Kind';
    case HAS_NO_CHILD = 'Spieler hat kein Kind';
    case HAS_SPECIFIC_CARD = 'Spieler hat die vorausgesetzte Karte';
    case NO_PREREQUISITES = 'Keine Voraussetzungen';
}
