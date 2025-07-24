<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\ValueObject;

enum PileId: string
{
    case BILDUNG_UND_KARRIERE_PHASE_1 = 'Bildung & Karriere | Phase 1';
    case BILDUNG_UND_KARRIERE_PHASE_2 = 'Bildung & Karriere | Phase 2';
    case BILDUNG_UND_KARRIERE_PHASE_3 = 'Bildung & Karriere | Phase 3';
    case SOZIALES_UND_FREIZEIT_PHASE_1 = 'Soziales & Freizeit | Phase 1';
    case SOZIALES_UND_FREIZEIT_PHASE_2 = 'Soziales & Freizeit | Phase 2';
    case SOZIALES_UND_FREIZEIT_PHASE_3 = 'Soziales & Freizeit | Phase 3';
    case JOBS_PHASE_1 = 'Jobs | Phase 1';
    case JOBS_PHASE_2 = 'Jobs | Phase 2';
    case JOBS_PHASE_3 = 'Jobs | Phase 3';
    case BILDUNG_UND_KARRIERE_PHASE_1_EREIGNISSE = 'Ereignisse Bildung & Karriere | Phase 1';
    case BILDUNG_UND_KARRIERE_PHASE_2_EREIGNISSE = 'Ereignisse Bildung & Karriere | Phase 2';
    case BILDUNG_UND_KARRIERE_PHASE_3_EREIGNISSE = 'Ereignisse Bildung & Karriere | Phase 3';
    case SOZIALES_UND_FREIZEIT_PHASE_1_EREIGNISSE = 'Ereignisse Soziales & Freizeit | Phase 1';
    case SOZIALES_UND_FREIZEIT_PHASE_2_EREIGNISSE = 'Ereignisse Soziales & Freizeit | Phase 2';
    case SOZIALES_UND_FREIZEIT_PHASE_3_EREIGNISSE = 'Ereignisse Soziales & Freizeit | Phase 3';
    case MINIJOBS = 'Minijobs';
}
