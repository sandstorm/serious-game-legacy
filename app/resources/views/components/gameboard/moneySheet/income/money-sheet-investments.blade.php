@use('Domain\Definitions\Investments\ValueObject\InvestmentId')
@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState')
@use('Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState')

@props([
    'immobilien' => [],
    'investments' => []
])

<div class="tabs__upper-content">
    @if ($investments || $immobilien)
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
                    <td>{!! $investment->price->format() !!}</td>
                    <td>
                        @if ($investment->investmentId === InvestmentId::MERFEDES_PENZ)
                            {!! KonjunkturphaseState::getCurrentKonjunkturphase($gameEvents)->getDividend()->format() !!}
                        @else
                            keine
                        @endif
                    </td>
                    <td>
                        {!! $investment->totalDividend->formatWithIcon() !!}
                    </td>
                </tr>
                @endif
            @endforeach
            @foreach($immobilien as $immobilie)
                <tr>
                    <td><i class="icon-immobilien" aria-hidden="true"></i></td>
                    <td>{{ $immobilie->getTitle() }}</td>
                    <td>1</td>
                    <td>{!! $immobilie->getPurchasePrice()->format() !!}</td>
                    <td>{!! $immobilie->getAnnualRent()->format() !!}</td>
                    <td>
                        {!! $immobilie->getAnnualRent()->formatWithIcon() !!}
                    </td>
                </tr>
            @endforeach
            <tr>
                <td colspan="5" class="text-align--right">Einnahmen gesamt</td>
                <td>{!! MoneySheetState::getAnnualIncomeForAllInvestments($gameEvents, $playerId)->formatWithIcon() !!}</td>
            </tr>
            </tbody>
        </table>
    @else
        <h4>Keine Aktien oder Immobilien vorhanden.</h4>
    @endif
</div>
