<?php

declare(strict_types=1);

namespace App\Livewire\ValueObject;

enum InvestitionenTabEnum: string
{
    case STOCKS = 'stocks';
    case ETF = 'etf';
    case IMMOBILIEN = 'immobilien';
    case EDELMETALLE = 'edelmetalle';
    case KRYTPO = 'krypto';
}
