<?php

declare(strict_types=1);

use Domain\CoreGameLogic\Feature\Moneysheet\State\LoanCalculator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\IsLoanAmountWithinLimitValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Command\DoMinijob;
use Domain\CoreGameLogic\Feature\Spielzug\Command\StartSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\TakeOutALoanForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\Dto\MinijobCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;
use Tests\DomainTestCase;

beforeEach(function () {
    /** @var DomainTestCase $this */
    $this->setupBasicGame();
});

describe('LoanCalculator::getMaxLoanAmountForPlayer', function () {
    it('is 80% of the Guthaben for a player without job, assets and loans', function () {
        /** @var DomainTestCase $this */
        expect(LoanCalculator::getMaxLoanAmountForPlayer($this->getGameEvents(), $this->players[0]))
            ->toEqual(new MoneyAmount(Configuration::STARTKAPITAL_VALUE * 0.8));
    });

    it('subtracts the open repayments of existing loans', function () {
        /** @var DomainTestCase $this */
        // total repayment for a loan of 10000 is 12000 -> (30000 + 10000) * 0.8 - 12000 = 20000
        $this->handle(TakeOutALoanForPlayer::create($this->players[0], 10000));

        expect(LoanCalculator::getMaxLoanAmountForPlayer($this->getGameEvents(), $this->players[0]))
            ->toEqual(new MoneyAmount(20000));
    });

    it('is only 50% of the Guthaben for a player who has filed for Insolvenz', function () {
        /** @var DomainTestCase $this */
        $this->setupInsolvenz();
        // WHY: after the Insolvenz the player has no money left, so 50% and 80% of it would be the same
        $this->startNewKonjunkturphaseWithCardsOnTop([
            new MinijobCardDefinition(
                id: new CardId('minijobAfterInsolvenz'),
                title: 'Minijob',
                description: 'for testing',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(10000),
                ),
            ),
        ]);
        $this->handle(new StartSpielzug($this->players[0]));
        $this->handle(DoMinijob::create($this->players[0]));

        $gameEvents = $this->getGameEvents();
        $guthaben = PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]);
        expect($guthaben->value)->toBeGreaterThan(0)
            ->and(LoanCalculator::getMaxLoanAmountForPlayer($gameEvents, $this->players[0]))
            ->toEqual(new MoneyAmount($guthaben->value * 0.5));
    });
});

describe('IsLoanAmountWithinLimitValidator', function () {
    it('fails if the loan amount is not positive', function (int $loanAmount) {
        /** @var DomainTestCase $this */
        $result = new IsLoanAmountWithinLimitValidator($loanAmount)->validate($this->getGameEvents(), $this->players[0]);
        expect($result->canExecute)->toBeFalse()
            ->and($result->reason)->toBe('Der Kreditbetrag muss größer als 0 sein.');
    })->with([0, -1]);

    it('succeeds if the loan amount is exactly the credit limit', function () {
        /** @var DomainTestCase $this */
        $result = new IsLoanAmountWithinLimitValidator((int) (Configuration::STARTKAPITAL_VALUE * 0.8))
            ->validate($this->getGameEvents(), $this->players[0]);
        expect($result->canExecute)->toBeTrue();
    });

    it('fails if the loan amount exceeds the credit limit', function () {
        /** @var DomainTestCase $this */
        $result = new IsLoanAmountWithinLimitValidator((int) (Configuration::STARTKAPITAL_VALUE * 0.8) + 1)
            ->validate($this->getGameEvents(), $this->players[0]);
        expect($result->canExecute)->toBeFalse()
            ->and($result->reason)->toBe('Du kannst keinen Kredit aufnehmen, der höher ist als das Kreditlimit.');
    });
});
