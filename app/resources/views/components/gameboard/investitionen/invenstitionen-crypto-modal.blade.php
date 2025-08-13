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
        <x-gameboard.investitionen.invenstitionen-buy-form
            :game-events="$gameEvents"
            :investment="InvestmentFinder::findInvestmentById($this->buyInvestmentOfType)"
            unit="Coin"
            buy-button-label="Coins kaufen"
        />
    @else
        <p>
            Kryptowährungen sind digitale, dezentral gehandelte Coins oder Tokens, deren Kurs allein durch Angebot und Nachfrage an Online-Börsen bestimmt wird.
            Sie versprechen hohe Renditechancen, gehen jedoch auch mit extremer Volatilität, regulatorischer Unsicherheit und technologischem Risiko einher.
        </p>
        <div class="investment-types">
            <x-gameboard.investitionen.investment-type
                :investment-type="InvestmentId::BAT_COIN"
                :game-Events="$gameEvents"
                unit="Coin"
            />

            <x-gameboard.investitionen.investment-type
                :investmentType="InvestmentId::MEME_COIN"
                :game-Events="$gameEvents"
                unit="Coin"
            />
        </div>
    @endif
@endsection
