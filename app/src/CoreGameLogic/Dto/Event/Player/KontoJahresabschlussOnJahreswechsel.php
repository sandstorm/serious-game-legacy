<?php
declare(strict_types=1);
namespace Domain\CoreGameLogic\Dto\Event\Player;

use Domain\CoreGameLogic\Dto\ValueObject\CurrentYear;
use Domain\CoreGameLogic\Dto\ValueObject\Leitzins;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;

readonly final class KontoJahresabschlussOnJahreswechsel
{

    public function __construct(
        public PlayerId    $playerId,
        public CurrentYear $year,

        // TODO: Summenbestandteile, ....
        public int         $kontoVeraenderungInEuro,
    )
    {
    }
}
