<?php

declare(strict_types=1);

namespace App\Livewire\Dto;

use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\ImmobilieId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

class ImmoblienDto
{
    public function __construct(
        protected string      $title,
        protected ImmobilieId $immobilieId,
        protected MoneyAmount $purchasePrice,
        protected MoneyAmount $annualRent,
    ) {
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getImmobilieId(): ImmobilieId
    {
        return $this->immobilieId;
    }

    public function getPurchasePrice(): MoneyAmount
    {
        return $this->purchasePrice;
    }

    public function getAnnualRent(): MoneyAmount
    {
        return $this->annualRent;
    }
}
