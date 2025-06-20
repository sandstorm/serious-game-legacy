<?php
declare(strict_types=1);
namespace Domain\CoreGameLogic\Feature\Moneysheet\Dto;


use Domain\Definitions\Card\ValueObject\MoneyAmount;

readonly final class InputResult //implements \JsonSerializable
{
    public function __construct(public bool $wasSuccessful, public MoneyAmount $fine = new MoneyAmount(0))
    {
    }
}
