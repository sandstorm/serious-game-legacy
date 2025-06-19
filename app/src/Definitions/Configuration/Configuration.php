<?php

declare(strict_types=1);

namespace Domain\Definitions\Configuration;

final readonly class Configuration
{
    const LEBENSHALTUNGSKOSTEN_MIN_VALUE = 5000;
    const LEBENSHALTUNGSKOSTEN_PERCENT = 35;
    const LEBENSHALTUNGSKOSTEN_MULTIPLIER = 0.35;
    const STEUERN_UND_ABGABEN_PERCENT = 25;
    const STEUERN_UND_ABGABEN_MULTIPLIER = 0.25;
    const FINE_VALUE = 250;
}
