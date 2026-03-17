<?php

declare(strict_types=1);

use Domain\CoreGameLogic\Feature\Spielzug\Command\DoMinijob;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\StartWeiterbildung;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SubmitAnswerForWeiterbildung;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\Dto\AnswerOption;
use Domain\Definitions\Card\Dto\WeiterbildungCardDefinition;
use Domain\Definitions\Card\ValueObject\AnswerId;
use Domain\Definitions\Card\ValueObject\CardId;
use Tests\TestCase;

beforeEach(function () {
    /** @var TestCase $this */
    $this->setupBasicGame();
});

describe('Weiterbildung Kompetenzstein awards', function () {
    it('awards 0.5 BildungsKompetenzstein for correct answer', function () {
        /** @var TestCase $this */
        $cardsForTesting = [
            new WeiterbildungCardDefinition(
                id: new CardId('wbTest'),
                description: 'Test Weiterbildung',
                answerOptions: [
                    new AnswerOption(new AnswerId("a"), "Correct answer", true),
                    new AnswerOption(new AnswerId("b"), "Wrong answer 1"),
                    new AnswerOption(new AnswerId("c"), "Wrong answer 2"),
                    new AnswerOption(new AnswerId("d"), "Wrong answer 3"),
                ],
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        $gameEvents = $this->getGameEvents();
        $initialKompetenzsteine = PlayerState::getBildungsKompetenzsteine($gameEvents, $this->players[0]);

        $this->handle(StartWeiterbildung::create($this->players[0]));
        $this->handle(SubmitAnswerForWeiterbildung::create($this->players[0], new AnswerId('a')));

        $gameEvents = $this->getGameEvents();
        expect(PlayerState::getBildungsKompetenzsteine($gameEvents, $this->players[0]))
            ->toEqual($initialKompetenzsteine + 0.5);
    });

    it('does not award BildungsKompetenzstein for incorrect answer', function () {
        /** @var TestCase $this */
        $cardsForTesting = [
            new WeiterbildungCardDefinition(
                id: new CardId('wbTest'),
                description: 'Test Weiterbildung',
                answerOptions: [
                    new AnswerOption(new AnswerId("a"), "Correct answer", true),
                    new AnswerOption(new AnswerId("b"), "Wrong answer 1"),
                    new AnswerOption(new AnswerId("c"), "Wrong answer 2"),
                    new AnswerOption(new AnswerId("d"), "Wrong answer 3"),
                ],
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        $gameEvents = $this->getGameEvents();
        $initialKompetenzsteine = PlayerState::getBildungsKompetenzsteine($gameEvents, $this->players[0]);

        $this->handle(StartWeiterbildung::create($this->players[0]));
        $this->handle(SubmitAnswerForWeiterbildung::create($this->players[0], new AnswerId('b')));

        $gameEvents = $this->getGameEvents();
        expect(PlayerState::getBildungsKompetenzsteine($gameEvents, $this->players[0]))
            ->toEqual($initialKompetenzsteine);
    });
});

describe('Weiterbildung across turns', function () {
    it('allows doing a Weiterbildung after a Minijob in a previous turn', function () {
        /** @var TestCase $this */
        $cardsForTesting = [
            new WeiterbildungCardDefinition(
                id: new CardId('wbTest'),
                description: 'Test Weiterbildung',
                answerOptions: [
                    new AnswerOption(new AnswerId("a"), "Correct answer", true),
                    new AnswerOption(new AnswerId("b"), "Wrong answer"),
                    new AnswerOption(new AnswerId("c"), "Wrong answer"),
                    new AnswerOption(new AnswerId("d"), "Wrong answer"),
                ],
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        // player 0 does a minijob (Zeitsteinaktion)
        $this->handle(DoMinijob::create($this->players[0]));
        $this->handle(new EndSpielzug($this->players[0]));

        // player 1 does mini job and ends turn
        $this->handle(DoMinijob::create($this->players[1]));
        $this->handle(new EndSpielzug($this->players[1]));

        // player 0 can now do a Weiterbildung (different turn)
        $this->handle(StartWeiterbildung::create($this->players[0]));
        $this->handle(SubmitAnswerForWeiterbildung::create($this->players[0], new AnswerId('a')));

        $gameEvents = $this->getGameEvents();
        expect(PlayerState::getBildungsKompetenzsteine($gameEvents, $this->players[0]))->toEqual(0.5);
    });

    it('allows doing a Minijob after a Weiterbildung in a previous turn', function () {
        /** @var TestCase $this */
        // player 0 does a Weiterbildung (Zeitsteinaktion)
        $this->handle(StartWeiterbildung::create($this->players[0]));
        $this->handle(SubmitAnswerForWeiterbildung::create($this->players[0], new AnswerId('a')));
        $this->handle(new EndSpielzug($this->players[0]));

        // player 1 does mini job and ends turn
        $this->handle(DoMinijob::create($this->players[1]));
        $this->handle(new EndSpielzug($this->players[1]));

        // player 0 can now do a Minijob (different turn)
        $gameEvents = $this->getGameEvents();
        $guthabenBefore = PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]);

        $this->handle(DoMinijob::create($this->players[0]));

        $gameEvents = $this->getGameEvents();
        $guthabenAfter = PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]);
        expect($guthabenAfter->value)->toBeGreaterThan($guthabenBefore->value);
    });
});
