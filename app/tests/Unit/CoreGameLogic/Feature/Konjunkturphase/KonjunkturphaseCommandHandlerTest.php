<?php
declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Konjunkturphase;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseDefinition;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryEnum;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphasenId;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphaseTypeEnum;

beforeEach(function () {
    $this->setupBasicGame();
});

describe('handleChangeKonjunkturphase', function () {
    it('redistributes Zeitsteine', function () {
        // Make sure the initial number of Zeitsteine is what we expect
        $expectedNumberOfZeitsteine = 6;
        /** @var GameEvents $stream */
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[0]))->toBe($expectedNumberOfZeitsteine);

        // use a Zeitstein
        $cardToActivate = array_shift($this->cardsBildung);
        $this->coreGameLogic->handle($this->gameId, ActivateCard::create($this->players[0], $cardToActivate->id, $cardToActivate->pileId, CategoryEnum::BILDUNG));
        /** @var GameEvents $stream */
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[0]))->toBe($expectedNumberOfZeitsteine-1);

        // Change Konjunkturphase
        $this->coreGameLogic->handle(
            $this->gameId,
            ChangeKonjunkturphase::create()->withFixedKonjunkturphaseForTesting(new KonjunkturphaseDefinition(
                id: KonjunkturphasenId::create(161),
                type: KonjunkturphaseTypeEnum::AUFSCHWUNG,
                description: 'no changes',
                additionalEvents: '',
                leitzins: 5,
                kompetenzbereiche: [],
                auswirkungen: []
            )));

        // Expect the number of Zeitsteine to be the initial value again
        /** @var GameEvents $stream */
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[0]))->toBe($expectedNumberOfZeitsteine);
    });

    it('happens automatically when no player has any Zeitsteine remaining', function () {
        // TODO implement
    })->skip('not yet implemented');
});
