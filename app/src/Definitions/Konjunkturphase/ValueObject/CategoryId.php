<?php

declare(strict_types=1);

namespace Domain\Definitions\Konjunkturphase\ValueObject;

enum CategoryId: string
{
    case BILDUNG_UND_KARRIERE = 'Bildung & Karriere';
    case SOZIALES_UND_FREIZEIT = 'Freizeit & Sozial';
    case EREIGNIS_BILDUNG_UND_KARRIERE = 'Ereignis: Bildung & Karriere';
    case EREIGNIS_SOZIALES_UND_FREIZEIT = 'Ereignis: Freizeit & Sozial';
    case INVESTITIONEN = 'Investitionen';
    case JOBS = 'Beruf';
    case MINIJOBS = 'Minijobs';
    case WEITERBILDUNG = 'Weiterbildung';
}
