@extends ('components.modal.modal', ['closeModal' => "toggleCryptoModal()"])

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
            Kauf - {{ $this->buyInvestmentOfType }} Kryptowährung <i class="icon-krypto" aria-hidden="true"></i>
        </span>
    @elseif ($this->sellInvestmentOfType)
        <span>
            Verkauf - {{ $this->sellInvestmentOfType }} <i class="icon-krypto" aria-hidden="true"></i>
        </span>
    @else
        <span>
            Kryptowährungen <i class="icon-krypto" aria-hidden="true"></i>
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
            unit="Coin"
            buy-button-label="Coins kaufen"
        />
    @elseif ($this->sellInvestmentOfType)
        <x-gameboard.investitionen.investitionen-sell-form
            :game-events="$gameEvents"
            action="sellInvestments('{{ $this->sellInvestmentOfType }}')"
            unit="Coin"
            sell-button-label="Coins verkaufen"
        />
    @else
        <p>
            Kryptowährungen sind digitale, dezentral gehandelte Coins oder Tokens, deren Kurs allein durch Angebot und Nachfrage an Online-Börsen bestimmt wird.
            Sie versprechen hohe Renditechancen, gehen jedoch auch mit extremer Volatilität, regulatorischer Unsicherheit und technologischem Risiko einher.
        </p>
        <div class="investitionen-types">
            <x-gameboard.investitionen.investitionen-type
                :investment-type="InvestmentId::BAT_COIN"
                :game-Events="$gameEvents"
                unit="Coin"
            />

            <x-gameboard.investitionen.investitionen-type
                :investmentType="InvestmentId::MEME_COIN"
                :game-Events="$gameEvents"
                unit="Coin"
            />
        </div>
    @endif
@endsection
