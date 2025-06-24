<?php

declare(strict_types=1);

namespace App\Livewire\ValueObject;

enum ExpensesTabEnum: string
{
    case LOANS = 'loans';
    case KIDS = 'kids';
    case INSURANCES = 'insurances';
    case TAXES = 'taxes';
    case LIVING_COSTS = 'livingCosts';
}
