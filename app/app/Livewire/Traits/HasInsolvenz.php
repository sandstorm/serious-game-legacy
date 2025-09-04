<?php

declare(strict_types=1);

namespace App\Livewire\Traits;


use App\Livewire\ValueObject\NotificationTypeEnum;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\CancelAllInsurancesForPlayerAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Command\CancelAllInsurancesForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\Definitions\Card\Dto\ResourceChanges;

trait HasInsolvenz
{
    public function cancelAllInsurances(): void
    {
        $validationResult = self::canCancelInsurances();
        if (!$validationResult->canExecute) {
            $this->showNotification(
                $validationResult->reason,
                NotificationTypeEnum::ERROR
            );
            return;
        }
        $totalCostOfInsurances = MoneySheetState::getCostOfAllInsurances($this->getGameEvents(), $this->myself);
        $this->coreGameLogic->handle($this->gameId, CancelAllInsurancesForPlayer::create(
            $this->myself,
        ));

        $this->showBanner(
            "Deine Versicherungen wurden gekündigt.",
            new ResourceChanges(guthabenChange: $totalCostOfInsurances)
        );
    }

    public function canCancelInsurances(): AktionValidationResult
    {
        $aktion = new CancelAllInsurancesForPlayerAktion();
        return $aktion->validate($this->myself, $this->getGameEvents());
    }

}
