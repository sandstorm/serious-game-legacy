<?php

declare(strict_types=1);

namespace App\Livewire\ValueObject;

enum IncomeTabEnum: string
{
    case INVESTMENTS = 'investments';
    case SALARY = 'salary';
}
