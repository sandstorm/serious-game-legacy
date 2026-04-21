<?php

declare(strict_types=1);

namespace Domain\Definitions\PlayerRole;

enum PlayerRole: string
{
    case VATER = 'Vater';
    case MUTTER = 'Mutter';
    case GESCHWISTER = 'Geschwister';
    case GROSSELTERN = 'Großeltern';
    case FREUNDE = 'Freunde';
    case ANDERE_FAMILIENMITGLIEDER = 'Andere Familienmitglieder';
}
