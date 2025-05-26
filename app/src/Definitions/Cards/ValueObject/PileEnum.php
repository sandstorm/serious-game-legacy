<?php

declare(strict_types=1);

namespace Domain\Definitions\Cards\ValueObject;

enum PileEnum: string
{
    case BILDUNG_PHASE_1 = 'Bildung & Karriere | Phase 1';
    case BILDUNG_PHASE_2 = 'Bildung & Karriere | Phase 2';
    case BILDUNG_PHASE_3 = 'Bildung & Karriere | Phase 3';
    case FREIZEIT_PHASE_1 = 'Freizeit & Sozial | Phase 1';
    case FREIZEIT_PHASE_2 = 'Freizeit & Sozial | Phase 2';
    case FREIZEIT_PHASE_3 = 'Freizeit & Sozial | Phase 3';
    case ERWERBSEINKOMMEN_PHASE_1 = 'Erwerbseinkommen | Phase 1';
    case ERWERBSEINKOMMEN_PHASE_2 = 'Erwerbseinkommen | Phase 2';
    case ERWERBSEINKOMMEN_PHASE_3 = 'Erwerbseinkommen | Phase 3';
}
