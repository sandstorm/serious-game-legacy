<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\GameReport\Dto;

enum HighlightType: string
{
    case MOST_MONEY = 'most-money';
    case BEST_INVESTMENT = 'best-investment';
    case WORST_INVESTMENT = 'worst-investment';
    case BIGGEST_CARD_EXPENSE = 'biggest-card-expense';
    case MOST_ACCURATE_MONEYSHEET = 'most-accurate-moneysheet';
    case MOST_FINES = 'most-fines';
    case INSURANCE_PAID_OFF = 'insurance-paid-off';
    case MOST_JOBS = 'most-jobs';
    case HIGHEST_SALARY = 'highest-salary';
    case MISSED_CHANCE = 'missed-chance';
}
