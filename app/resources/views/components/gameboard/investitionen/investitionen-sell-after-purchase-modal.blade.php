@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\InvestmentPriceState')

@props([
    'playerId' => null,
    'game-events' => null,
])

<x-modal.mandatory-modal size="small">
    <x-slot:icon>
        <i class="icon-ereignis" aria-hidden="true"></i>
    </x-slot:icon>

    <x-slot:title>
        <span>
            Verkauf - {{ $this->sellInvestmentsForm->investmentId }} <i class="icon-aktien" aria-hidden="true"></i>
        </span>
    </x-slot:title>

    <h4>{{ $this->sellInvestmentsForm->playerName }} hat in {{ $this->sellInvestmentsForm->investmentId }} investiert!</h4>
    @if ($this->sellInvestmentsForm->amountOwned > 0)
        <p>
            Du kannst jetzt deine Anteile verkaufen.
        </p>
    @endif

    <x-gameboard.investitionen.investitionen-sell-form
        :game-events="$gameEvents"
        unit="Anteil"
        sell-button-label="Anteile verkaufen"
        action="sellInvestmentsAfterPurchase('{{ $this->sellInvestmentsForm->investmentId }}')"
        :does-cost-zeitstein="false"
    />

    <x-slot:footer>
        <button type="button"
                @class([
                    "button",
                    "button--type-outline-primary",
                    $this->getPlayerColorClass()
                ])
                wire:click="closeSellInvestmentsModal()"
        >
            Ich möchte nichts verkaufen
        </button>
    </x-slot:footer>
</x-modal.mandatory-modal>
