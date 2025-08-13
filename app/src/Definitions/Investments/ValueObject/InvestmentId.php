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

    public static function fromString(string $value): self
    {
        return match ($value) {
            'Merfedes-Penz' => self::MERFEDES_PENZ,
            'BetaPear' => self::BETA_PEAR,
            'ETF-MSCI-World' => self::ETF_MSCI_WORLD,
            'ETF-Clean-Energy' => self::ETF_CLEAN_ENERGY,
            'Bat-Coin' => self::BAT_COIN,
            'Meme-Coin' => self::MEME_COIN,
            default => throw new \InvalidArgumentException('Invalid StockType: ' . $value),
        };
    }

}
