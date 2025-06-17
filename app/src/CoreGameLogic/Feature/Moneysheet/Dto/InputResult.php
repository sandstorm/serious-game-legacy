<?php
declare(strict_types=1);
namespace Domain\CoreGameLogic\Feature\Moneysheet\Dto;


readonly final class InputResult //implements \JsonSerializable
{
    public function __construct(public bool $wasSuccessful, public float $fine = 0)
    {
    }
}
