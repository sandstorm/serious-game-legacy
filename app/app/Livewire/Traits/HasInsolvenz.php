<?php

declare(strict_types=1);

namespace App\Livewire\Traits;


use App\Livewire\Forms\SellInvestmentsForm;
use App\Livewire\ValueObject\NotificationTypeEnum;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\InvestmentPriceState;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\CancelAllInsurancesForPlayerAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\SellInvestmentsForInsolvenzForPlayerAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Command\CancelAllInsurancesForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SellInvestmentsForInsolvenzForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\InvestmentsWereSoldForInsolvenzForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Investments\ValueObject\InvestmentId;

trait HasInsolvenz
{
    public bool $sellAllInvestmentsModalIsVisible = false;
    public SellInvestmentsForm $sellInvestmentsForm;
    public ?InvestmentId $sellInvestmentOfType = null;

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

    public function toggleSellAllInvestmentsModal(): void
    {
        $this->sellAllInvestmentsModalIsVisible = !$this->sellAllInvestmentsModalIsVisible;
    }

    public function showSellInvestmentOfTypeForInsolvenz(string $investmentId): void
    {
        $this->sellInvestmentsForm->reset();
        $this->sellInvestmentsForm->resetValidation();

        $investmentId = InvestmentId::from($investmentId);

        $validationResult = self::canSellInvestmentsForInsolvenz($investmentId);
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

    public function sellInvestmentsForInsolvenz(string $investmentId): void
    {
        $this->sellInvestmentsForm->validate();
        $investmentId = InvestmentId::from($investmentId);

        $validationResult = self::canSellInvestmentsForInsolvenz($investmentId);
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

        $this->coreGameLogic->handle($this->gameId, SellInvestmentsForInsolvenzForPlayer::create(
            $this->myself,
            $investmentId,
            $this->sellInvestmentsForm->amount
        ));

        $this->closeInvestmentModals();
        $this->broadcastNotify();

        /** @var InvestmentsWereSoldForInsolvenzForPlayer $event */
        $event = $this->getGameEvents()->findLast(InvestmentsWereSoldForInsolvenzForPlayer::class);
        $this->showBanner($event->amount . ' Anteile von ' . $investmentId->value . ' wurden erfolgreich verkauft.', $event->getResourceChanges($this->myself));
    }

    public function canSellInvestmentsForInsolvenz(InvestmentId $investmentId): AktionValidationResult
    {
        $aktion = new SellInvestmentsForInsolvenzForPlayerAktion(
            $investmentId,
            InvestmentPriceState::getCurrentInvestmentPrice($this->getGameEvents(), $investmentId),
            $this->sellInvestmentsForm->amount ?? 0
        );
        return $aktion->validate($this->myself, $this->getGameEvents());
    }
}
