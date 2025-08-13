<?php

declare(strict_types=1);

namespace Domain\Definitions\Investments\ValueObject;

enum InvestmentId: string
{
    case MERFEDES_PENZ = 'Merfedes-Penz';
    case BETA_PEAR = 'BetaPear';
    case ETF_MSCI_WORLD = 'ETF-MSCI-World';
    case ETF_CLEAN_ENERGY = 'ETF-Clean-Energy';
    case BAT_COIN = 'Bat-Coin';
    case MEME_COIN = 'Meme-Coin';
}
