<?php

declare(strict_types=1);

namespace App\View\Components\MoneySheet;

use App\Livewire\Dto\MoneySheet as MoneySheetDto;
use Illuminate\View\Component;
use Illuminate\View\View;

class MoneySheet extends Component
{
    /**
     * Create the component instance.
     */
    public function __construct(
        public MoneySheetDto $moneySheet,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.gameboard.moneySheet.money-sheet-modal', [
            'moneySheet' => $this->moneySheet,
        ]);
    }

}
