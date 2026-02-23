<?php

declare(strict_types=1);

namespace Domain\Definitions\Configuration;

final readonly class Configuration
{
    /**
     * The initial capital for each player
     * @var int
     */
    final public const STARTKAPITAL_VALUE = 30000;

    /**
     * minimum value for the Lebenshaltungskosten
     * @var int
     */
    final public const LEBENSHALTUNGSKOSTEN_MIN_VALUE = 5000;
    final public const LEBENSHALTUNGSKOSTEN_DEFAULT_VALUE = 0;

    /**
     * percent value for Lebenshaltungskosten (can be used to display)
     * @see LEBENSHALTUNGSKOSTEN_MULTIPLIER
     */
    final public const float LEBENSHALTUNGSKOSTEN_PERCENT = 35;

    /**
     * percent value divided by 100 for easier use in calculations
     * @see LEBENSHALTUNGSKOSTEN_PERCENT
     */
    final public const LEBENSHALTUNGSKOSTEN_MULTIPLIER = 0.35;

    /**
     * percent value for Steuern und Abgaben (can be used to display)
     * @see STEUERN_UND_ABGABEN_MULTIPLIER
     */
    final public const STEUERN_UND_ABGABEN_PERCENT = 25;

    /**
     * percent value divided by 100 for easier use in calculations
     * @see STEUERN_UND_ABGABEN_PERCENT
     */
    final public const STEUERN_UND_ABGABEN_MULTIPLIER = 0.25;

    /**
     * the default value for the 'Steuern und Abgaben' input field
     * @var int
     */
    final public const STEUERN_UND_ABGABEN_DEFAULT_VALUE = 0;

    /**
     * number of tries a player has to correctly fill an input, before FINE_VALUE will be deducted from
     * their balance.
     * @var int
     */
    final public const MAX_NUMBER_OF_TRIES_PER_INPUT = 2;

    /**
     * amount of money that will be deducted from the players balance for entering the wrong value.
     * MAX_NUMBER_OF_TRIES_PER_INPUT times.
     * @var int
     */
    final public const FINE_VALUE = 500;

    /**
     * the number of Konjunkturphasen an loan will be repaid over.
     * @var int
     */
    final public const REPAYMENT_PERIOD = 20;

    /**
     * The initial investment price for the stock/etf/crypto market.
     * @var float
     */
    final public const INITIAL_INVESTMENT_PRICE = 50.0;

    /**
     * The maximum input value for number input fields.
     * This is to prevent integer overflow issues in laravel forms
     * @var int
     */
    final public const MAX_INPUT_VALUE = 2147483647;

    /**
     * The duration of an insolvenz in years.
     */
    final public const INSOLVENZ_DURATION = 3;

    /**
     * The maximum amount of money a player can keep from their income when they are insolvent.
     */
    final public const INSOLVENZ_PFAENDUNGSFREIGRENZE = 10000;
}
