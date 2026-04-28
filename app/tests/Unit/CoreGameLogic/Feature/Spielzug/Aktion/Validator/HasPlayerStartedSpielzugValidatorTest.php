<?php

declare(strict_types=1);

use Domain\CoreGameLogic\Feature\Spielzug\Aktion\AcceptJobOfferAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\ActivateCardAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\BuyInvestmentsForPlayerAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\DoMinijobAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\SkipCardAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Command\StartSpielzug;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Investments\ValueObject\InvestmentId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Tests\TestCase;

beforeEach(function () {
    /** @var TestCase $this */
    $this->setupBasicGame();
});

describe('HasPlayerStartedSpielzugValidator (regression test for issue #652)', function () {
    it('blocks DoMinijobAktion before turn started', function () {
        /** @var TestCase $this */
        $result = new DoMinijobAktion()->validate($this->players[0], $this->getGameEvents());
        expect($result->canExecute)->toBeFalse()
            ->and($result->reason)->toBe('Du musst deinen Spielzug erst starten.');
    });

    it('blocks ActivateCardAktion before turn started', function () {
        /** @var TestCase $this */
        $result = new ActivateCardAktion(CategoryId::BILDUNG_UND_KARRIERE)
            ->validate($this->players[0], $this->getGameEvents());
        expect($result->canExecute)->toBeFalse()
            ->and($result->reason)->toBe('Du musst deinen Spielzug erst starten.');
    });

    it('blocks SkipCardAktion before turn started', function () {
        /** @var TestCase $this */
        $result = new SkipCardAktion(CategoryId::BILDUNG_UND_KARRIERE)
            ->validate($this->players[0], $this->getGameEvents());
        expect($result->canExecute)->toBeFalse()
            ->and($result->reason)->toBe('Du musst deinen Spielzug erst starten.');
    });

    it('blocks BuyInvestmentsForPlayerAktion before turn started', function () {
        /** @var TestCase $this */
        $result = new BuyInvestmentsForPlayerAktion(InvestmentId::BETA_PEAR, new MoneyAmount(50), 1)
            ->validate($this->players[0], $this->getGameEvents());
        expect($result->canExecute)->toBeFalse()
            ->and($result->reason)->toBe('Du musst deinen Spielzug erst starten.');
    });

    it('blocks AcceptJobOfferAktion before turn started', function () {
        /** @var TestCase $this */
        $result = new AcceptJobOfferAktion(new CardId('j100'))
            ->validate($this->players[0], $this->getGameEvents());
        expect($result->canExecute)->toBeFalse()
            ->and($result->reason)->toBe('Du musst deinen Spielzug erst starten.');
    });

    it('lets actions through once SpielzugWasStarted has been emitted', function () {
        /** @var TestCase $this */
        $this->coreGameLogic->handle($this->gameId, new StartSpielzug($this->players[0]));

        $result = new DoMinijobAktion()->validate($this->players[0], $this->getGameEvents());

        // The new validator must not block once the turn is started; later validators may still
        // gate the action for unrelated reasons, but the start gate is open.
        expect($result->reason ?? '')->not->toBe('Du musst deinen Spielzug erst starten.');
    });
});
