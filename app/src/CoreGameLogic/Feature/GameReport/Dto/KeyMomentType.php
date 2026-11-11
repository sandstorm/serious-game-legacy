<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\GameReport\Dto;

enum KeyMomentType: string
{
    case LEBENSZIELPHASE_CHANGED = 'lebenszielphase-changed';
    case LEBENSZIEL_FINISHED = 'lebensziel-finished';
    case EREIGNIS = 'ereignis';
    case INSOLVENZ = 'insolvenz';
    case LOAN_TAKEN = 'loan-taken';
    case LOAN_REPAID = 'loan-repaid';
    case IMMOBILIE_BOUGHT = 'immobilie-bought';
    case IMMOBILIE_SOLD = 'immobilie-sold';
    case BIG_INVESTMENT_TRADE = 'big-investment-trade';
}
