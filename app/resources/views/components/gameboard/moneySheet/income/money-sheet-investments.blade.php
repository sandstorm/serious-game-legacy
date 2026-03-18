@use('Domain\Definitions\Investments\ValueObject\InvestmentId')
@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState')
@use('Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState')

@props([
    'immobilienOwnedByPlayer' => [],
    'investments' => []
])

<div class="tabs__upper-content">
    @if (count($immobilienOwnedByPlayer) > 0 || count($investments) > 0)
        <table>
            <thead>
            <tr>
                <th></th>
                <th>Anlageart</th>
                <th>Menge</th>
                <th>Aktueller Preis/ Kaufpreis</th>
                <th>Dividende/Stück oder Mietertrag</th>
                <th>Einnahmen</th>
            </tr>
            </thead>
            <tbody>
            @foreach($investments as $investment)
                @if ($investment->amount > 0)
                <tr>
                    <td><i class="icon-aktien" aria-hidden="true"></i></td>
                    <td>{{ $investment->investmentId }}</td>
                    <td>{{ $investment->amount }}</td>
                    <td><x-money-amount :value="$investment->price" /></td>
                    <td>
                        @if ($investment->investmentId === InvestmentId::MERFEDES_PENZ)
                            <x-money-amount :value="KonjunkturphaseState::getCurrentKonjunkturphase($gameEvents)->getDividend()" />
                        @else
                            keine
                        @endif
                    </td>
                    <td>
                        <x-money-amount :value="$investment->totalDividend" with-icon />
                    </td>
                </tr>
                @endif
            @endforeach
            @foreach($immobilienOwnedByPlayer as $immobilie)
                <tr>
                    <td><i class="icon-immobilien" aria-hidden="true"></i></td>
                    <td>{{ $immobilie->getTitle() }}</td>
                    <td>1</td>
                    <td><x-money-amount :value="$immobilie->getPurchasePrice()" /></td>
                    <td><x-money-amount :value="$immobilie->getAnnualRent()" /></td>
                    <td>
                        <x-money-amount :value="$immobilie->getAnnualRent()" with-icon />
                    </td>
                </tr>
            @endforeach
            <tr>
                <td colspan="5" class="text-align--right">Einnahmen gesamt</td>
                <td><x-money-amount :value="MoneySheetState::getAnnualIncomeForAllInvestments($gameEvents, $playerId)" with-icon /></td>
            </tr>
            </tbody>
        </table>
    @else
        <h4>Keine Investitionen vorhanden.</h4>
    @endif
</div>
