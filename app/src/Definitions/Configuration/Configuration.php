<?php

declare(strict_types=1);

namespace Domain\Definitions\Configuration;

final readonly class Configuration
{
    /**
     * The initial capital for each player
     * @var int
     */
    public final const STARTKAPITAL_VALUE = 50000;

    /**
     * start value for Zeitsteine for 2,3,4 players
     * @var int
     */
    public final const INITIAL_AMOUNT_OF_ZEITSTEINE_FOR_TWO_PLAYERS = 5;
    public final const INITIAL_AMOUNT_OF_ZEITSTEINE_FOR_THREE_OR_FOUR_PLAYERS = 4;

    /**
     * minimum value for the Lebenshaltungskosten
     * @var int
     */
    public final const LEBENSHALTUNGSKOSTEN_MIN_VALUE = 5000;
    public final const LEBENSHALTUNGSKOSTEN_DEFAULT_VALUE = 0;

    /**
     * percent value for Lebenshaltungskosten (can be used to display)
     * @see LEBENSHALTUNGSKOSTEN_MULTIPLIER
     */
    public final const LEBENSHALTUNGSKOSTEN_PERCENT = 35;

    /**
     * percent value divided by 100 for easier use in calculations
     * @see LEBENSHALTUNGSKOSTEN_PERCENT
     */
    public final const LEBENSHALTUNGSKOSTEN_MULTIPLIER = 0.35;

    /**
     * percent value for Steuern und Abgaben (can be used to display)
     * @see STEUERN_UND_ABGABEN_MULTIPLIER
     */
    public final const STEUERN_UND_ABGABEN_PERCENT = 25;

    /**
     * percent value divided by 100 for easier use in calculations
     * @see STEUERN_UND_ABGABEN_PERCENT
     */
    public final const STEUERN_UND_ABGABEN_MULTIPLIER = 0.25;

    /**
     * the default value for the 'Steuern und Abgaben' input field
     * @var int
     */
    public final const STEUERN_UND_ABGABEN_DEFAULT_VALUE = 0;

    /**
     * number of tries a player has to correctly fill an input, before FINE_VALUE will be deducted from
     * their balance.
     * @var int
     */
    public final const MAX_NUMBER_OF_TRIES_PER_INPUT = 2;

    /**
     * amount of money that will be deducted from the players balance for entering the wrong value.
     * MAX_NUMBER_OF_TRIES_PER_INPUT times.
     * @var int
     */
    public final const FINE_VALUE = 250;

    /**
     * the number of Konjunkturphasen an loan will be repaid over.
     * @var int
     */
    public final const REPAYMENT_PERIOD = 20;

}
