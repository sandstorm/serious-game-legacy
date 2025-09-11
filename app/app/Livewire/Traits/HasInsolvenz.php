<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\Forms\SellInvestmentsForm;
use App\Livewire\ValueObject\NotificationTypeEnum;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\InvestmentPriceState;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\CancelAllInsurancesToAvoidInsolvenzForPlayerAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\FileInsolvenzForPlayerAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\SellInvestmentsToAvoidInsolvenzForPlayerAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Command\CancelAllInsurancesToAvoidInsolvenzForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\FileInsolvenzForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SellInvestmentsToAvoidInsolvenzForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\InvestmentsWereSoldToAvoidInsolvenzForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Investments\ValueObject\InvestmentId;

trait HasInsolvenz
{
    public bool $isSellInvestmentsToAvoidInsolvenzModalVisible = false;
    public bool $isShowInformationForFiledInsolvenzModalVisible = false;
    public SellInvestmentsForm $sellInvestmentsForm;
    public ?InvestmentId $sellInvestmentOfType = null;

    public function canCancelInsurances(): AktionValidationResult
    {
        $aktion = new CancelAllInsurancesToAvoidInsolvenzForPlayerAktion();
        return $aktion->validate($this->myself, $this->getGameEvents());
    }

    public function cancelAllInsurancesToAvoidInsolvenz(): void
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
        $this->handleCommand(CancelAllInsurancesToAvoidInsolvenzForPlayer::create(
            $this->myself,
        ));

        $this->showBanner(
            "Deine Versicherungen wurden gekündigt.",
            new ResourceChanges(guthabenChange: $totalCostOfInsurances)
        );
    }

    public function toggleSellInvestmentsToAvoidInsolvenzModal(): void
    {
        $this->isSellInvestmentsToAvoidInsolvenzModalVisible = !$this->isSellInvestmentsToAvoidInsolvenzModalVisible;
    }

    public function showSellInvestmentOfTypeToAvoidInsolvenz(string $investmentId): void
    {
        $this->sellInvestmentsForm->reset();
        $this->sellInvestmentsForm->resetValidation();

        $investmentId = InvestmentId::from($investmentId);

        $validationResult = self::canSellInvestmentsToAvoidInsolvenz($investmentId);
        if (!$validationResult->canExecute) {
            $this->showNotification(
                "Aktien verkaufen nicht möglich: " . $validationResult->reason,
                NotificationTypeEnum::ERROR
            );
            return;
        }

        $this->sellInvestmentOfType = $investmentId;
        $this->sellInvestmentsForm->investmentId = $investmentId;
        $this->sellInvestmentsForm->sharePrice = InvestmentPriceState::getCurrentInvestmentPrice($this->getGameEvents(), $investmentId)->value;
        $this->sellInvestmentsForm->amountOwned = PlayerState::getAmountOfAllInvestmentsOfTypeForPlayer(
            $this->getGameEvents(),
            $this->myself,
            $investmentId
        );
    }

    public function canSellInvestmentsToAvoidInsolvenz(InvestmentId $investmentId): AktionValidationResult
    {
        $aktion = new SellInvestmentsToAvoidInsolvenzForPlayerAktion(
            $investmentId,
            InvestmentPriceState::getCurrentInvestmentPrice($this->getGameEvents(), $investmentId),
            $this->sellInvestmentsForm->amount ?? 0
        );
        return $aktion->validate($this->myself, $this->getGameEvents());
    }

    public function sellInvestmentsToAvoidInsolvenz(string $investmentId): void
    {
        $this->sellInvestmentsForm->validate();
        $investmentId = InvestmentId::from($investmentId);

        $validationResult = self::canSellInvestmentsToAvoidInsolvenz($investmentId);
        if (!$validationResult->canExecute) {
            $this->showNotification(
                "Aktien verkaufen nicht möglich: " . $validationResult->reason,
                NotificationTypeEnum::ERROR
            );
            return;
        }

        // Amount should not ever be null, but just in case and to fix phpstan errors
        if ($this->sellInvestmentsForm->amount === null) {
            return;
        }

        $this->handleCommand(SellInvestmentsToAvoidInsolvenzForPlayer::create(
            $this->myself,
            $investmentId,
            $this->sellInvestmentsForm->amount
        ));

        $this->closeInvestmentModals();
        $this->broadcastNotify();

        /** @var InvestmentsWereSoldToAvoidInsolvenzForPlayer|null $event */
        $event = $this->getGameEvents()->findLastOrNullWhere(
            fn($e) => $e instanceof InvestmentsWereSoldToAvoidInsolvenzForPlayer && $e->getPlayerId()->equals($this->myself)
        );
        if ($event !== null) {
            $this->showBanner($event->getAmount() . ' Anteile von ' . $investmentId->value . ' wurden erfolgreich verkauft.', $event->getResourceChanges($this->myself));
        }
    }

    public function canFileInsolvenzForPlayer(): AktionValidationResult
    {
        $aktion = new FileInsolvenzForPlayerAktion();
        return $aktion->validate($this->myself, $this->getGameEvents());
    }

    public function fileInsolvenzForPlayer(): void
    {
        $validationResult = self::canFileInsolvenzForPlayer();
        if(!$validationResult->canExecute) {
            $this->showNotification(
                $validationResult->reason,
                NotificationTypeEnum::ERROR
            );
            return;
        }
        $this->handleCommand(FileInsolvenzForPlayer::create($this->myself));
        $this->isShowInformationForFiledInsolvenzModalVisible = true;
        $this->broadcastNotify();
    }

    public function toggleShowInformationForFiledInsolvenzModal(): void
    {
        $this->isShowInformationForFiledInsolvenzModalVisible = !$this->isShowInformationForFiledInsolvenzModalVisible;
    }
}
