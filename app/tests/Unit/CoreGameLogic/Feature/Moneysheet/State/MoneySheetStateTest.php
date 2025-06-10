<?php
declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Moneysheet\State;

use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\AcceptJobOffer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\RequestJobOffers;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\JobRequirements;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\Gehalt;
use Domain\Definitions\Card\ValueObject\PileId;
use Tests\TestCase;

beforeEach(function () {
    /** @var TestCase $this */
    $this->setupBasicGame();
});

describe('calculateLebenshaltungskostenForPlayer', function () {
    it('returns 5000 when player has no job', function (){
        /** @var TestCase $this */
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actualKosten = MoneySheetState::calculateLebenshaltungskostenForPlayer($gameEvents, $this->players[0]);
        expect($actualKosten)->toBe(5000);
    });

    it('returns 5000 when 35% of the Gehalt is less than 5000', function (){
        /** @var TestCase $this */

        CardFinder::getInstance()->overrideCardsForTesting([
            PileId::JOBS_PHASE_1->value => [
                "j0" => new JobCardDefinition(
                    id: new CardId('j0'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'offered 1',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new Gehalt(14000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                    ),
                ),
            ]
        ]);

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('j0')));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actualKosten = MoneySheetState::calculateLebenshaltungskostenForPlayer($gameEvents, $this->players[0]);
        expect($actualKosten)->toBe(5000);
    });

    it('returns 35% of the Gehalt it that is more than 5000', function (){
        /** @var TestCase $this */

        CardFinder::getInstance()->overrideCardsForTesting([
            PileId::JOBS_PHASE_1->value => [
                "j0" => new JobCardDefinition(
                    id: new CardId('j0'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'offered 1',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new Gehalt(34000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                    ),
                ),
            ]
        ]);

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('j0')));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actualKosten = MoneySheetState::calculateLebenshaltungskostenForPlayer($gameEvents, $this->players[0]);
        expect($actualKosten)->toBe(11900);
    });
});

describe('calculateSteuernUndAbgabenForPlayer', function () {
    it('returns 0 when player has no job', function (){
        /** @var TestCase $this */
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actualKosten = MoneySheetState::calculateSteuernUndAbgabenForPlayer($gameEvents, $this->players[0]);
        expect($actualKosten)->toBe(0);
    });

    it('returns 35% of the Gehalt if the player has a job', function (){
        /** @var TestCase $this */

        CardFinder::getInstance()->overrideCardsForTesting([
            PileId::JOBS_PHASE_1->value => [
                "j0" => new JobCardDefinition(
                    id: new CardId('j0'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'offered 1',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new Gehalt(34000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                    ),
                ),
            ]
        ]);

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('j0')));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actualKosten = MoneySheetState::calculateSteuernUndAbgabenForPlayer($gameEvents, $this->players[0]);
        expect($actualKosten)->toBe(8500);
    });
});

