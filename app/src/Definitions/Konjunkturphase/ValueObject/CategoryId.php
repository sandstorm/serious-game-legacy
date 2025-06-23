<?php

declare(strict_types=1);

namespace Domain\Definitions\Konjunkturphase\ValueObject;

enum CategoryId: string
{
    case BILDUNG_UND_KARRIERE = 'Bildung & Karriere';
    case SOZIALES_UND_FREIZEIT = 'Freizeit & Sozial';
    case INVESTITIONEN = 'Investitionen';
    case JOBS = 'Job';
}
