@extends ('components.modal.modal', ['closeModal' => "toggleETFModal()"])

@use('Domain\Definitions\Investments\ValueObject\InvestmentId')
@use('Domain\Definitions\Investments\InvestmentFinder')

@props([
    'gameEvents' => null,
])

@section('icon')
    <x-gameboard.phase-icon />
@endsection

@section('title')
    @if ($this->buyInvestmentOfType)
        <span>
            Kauf - {{ $this->buyInvestmentOfType }} <i class="icon-ETF" aria-hidden="true"></i>
        </span>
    @else
        <span>
            ETF <i class="icon-ETF" aria-hidden="true"></i>
        </span>
        <span class="font-size--base">
            Investitionen
        </span>
    @endif
@endsection

@section('content')
    @if ($this->buyInvestmentOfType)
        <x-gameboard.investitionen.invenstitionen-buy-form
            :game-events="$gameEvents"
            :investment="InvestmentFinder::findInvestmentById($this->buyInvestmentOfType)"
            unit="ETF"
            buy-button-label="ETFs kaufen"
        />
    @else
        <p>
            ETFs sind börsengehandelte Fonds, die das Kapital vieler Anleger bündeln, um breit gestreut in verschiedene Wertpapiere zu investieren.
            Sie bilden dabei automatisch ganze Marktindizes (z. B. den Deutschen Aktienindex DAX) oder spezifische Themenbereiche (z. B. erneuerbare Energien) ab.
            Diese breite Streuung reduziert Einzelrisiken, allerdings hängt die Wertentwicklung weiterhin von der allgemeinen Markt- oder Branchenentwicklung ab.
        </p>
        <div class="investment-types">
            <x-gameboard.investitionen.investment-type
                :investment-type="InvestmentId::ETF_MSCI_WORLD"
                :game-Events="$gameEvents"
                unit="ETF"
            />

            <x-gameboard.investitionen.investment-type
                :investmentType="InvestmentId::ETF_CLEAN_ENERGY"
                :game-Events="$gameEvents"
                unit="ETF"
            />
        </div>
    @endif
@endsection
