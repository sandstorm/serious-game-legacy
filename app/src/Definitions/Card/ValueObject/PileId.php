<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\ValueObject;

enum PileId: string
{
    case BILDUNG_PHASE_1 = 'Bildung & Karriere | Phase 1';
    case BILDUNG_PHASE_2 = 'Bildung & Karriere | Phase 2';
    case BILDUNG_PHASE_3 = 'Bildung & Karriere | Phase 3';
    case FREIZEIT_PHASE_1 = 'Freizeit & Sozial | Phase 1';
    case FREIZEIT_PHASE_2 = 'Freizeit & Sozial | Phase 2';
    case FREIZEIT_PHASE_3 = 'Freizeit & Sozial | Phase 3';
    case JOBS_PHASE_1 = 'Jobs | Phase 1';
    case JOBS_PHASE_2 = 'Jobs | Phase 2';
    case JOBS_PHASE_3 = 'Jobs | Phase 3';
    case MINIJOBS_PHASE_1 = 'Minijobs | Phase 1';
}
