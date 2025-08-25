@extends ('components.modal.modal', ['closeModal' => "toggleStocksModal()"])

@use('Domain\Definitions\Investments\ValueObject\InvestmentId')
@use('Domain\Definitions\Investments\InvestmentFinder')

@props([
    'gameEvents' => null,
])

@section('icon')
    <x-gameboard.phase-icon/>
@endsection

@section('title')
    @if ($this->buyInvestmentOfType)
        <span>
            Kauf - {{ $this->buyInvestmentOfType }} <i class="icon-aktien" aria-hidden="true"></i>
        </span>
    @elseif ($this->sellInvestmentOfType)
        <span>
            Verkauf - {{ $this->sellInvestmentOfType }} <i class="icon-aktien" aria-hidden="true"></i>
        </span>
    @else
        <span>
            Aktien <i class="icon-aktien" aria-hidden="true"></i>
        </span>
        <span class="font-size--base">
            Investitionen
        </span>
    @endif
@endsection

@section('content')
    @if ($this->buyInvestmentOfType)
        <x-gameboard.investitionen.investitionen-buy-form
            :game-events="$gameEvents"
            :investment="InvestmentFinder::findInvestmentById($this->buyInvestmentOfType)"
        />
    @elseif ($this->sellInvestmentOfType)
        <x-gameboard.investitionen.investitionen-sell-form
            :game-events="$gameEvents"
            action="sellInvestments('{{ $this->sellInvestmentOfType }}')"
        />
    @else
        <p>
            Aktien sind Anteilsscheine an einzelnen Unternehmen. Ihr Wert schwankt abhängig von
            Gewinnen, Management-Entscheidungen und aktuellen Nachrichten. Sie bieten Chancen auf
            Dividenden und Kursgewinne, bergen jedoch auch das Risiko unternehmensspezifischer Rückschläge.
        </p>
        <div class="investitionen-types">
            <x-gameboard.investitionen.investitionen-type
                :investment-type="InvestmentId::MERFEDES_PENZ"
                :game-Events="$gameEvents"/>

            <x-gameboard.investitionen.investitionen-type
                :investmentType="InvestmentId::BETA_PEAR"
                :game-Events="$gameEvents"/>
        </div>
    @endif
@endsection
