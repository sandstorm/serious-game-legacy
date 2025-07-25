<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\PileState;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerDoneNoZeitsteinaktionThisTurnValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerEnoughZeitsteineValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\IsPlayersTurnValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\WeiterbildungWasStarted;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\Dto\WeiterbildungCardDefinition;
use Domain\Definitions\Card\ValueObject\PileId;
use Random\Randomizer;
use RuntimeException;

class StartWeiterbildungAktion extends Aktion
{

    public function __construct()
    {
        parent::__construct('start-weiterbildung', 'Weiterbildung starten');
    }

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $validatorChain = new IsPlayersTurnValidator();
        $validatorChain
            ->setNext(new HasPlayerEnoughZeitsteineValidator(1))
            ->setNext(new HasPlayerDoneNoZeitsteinaktionThisTurnValidator());

        return $validatorChain->validate($gameEvents, $playerId);
    }

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($playerId, $gameEvents);
        if (!$result->canExecute) {
            throw new RuntimeException('Cannot start weiterbildung: ' . $result->reason, 1753087476
            );
        }
        $topCardOnPile = PileState::topCardIdForPile($gameEvents, PileId::WEITERBILDUNG_PHASE_1);

        /** @var WeiterbildungCardDefinition $weiterbildungCardDefinition */
        $weiterbildungCardDefinition = CardFinder::getInstance()->getCardById($topCardOnPile);
        $weiterbildungsOptions = (new Randomizer())->shuffleArray($weiterbildungCardDefinition->answerOptions);

        return GameEventsToPersist::with(
            new WeiterbildungWasStarted(
                playerId: $playerId,
                weiterbildungCardId: $weiterbildungCardDefinition->id,
                resourceChanges: new ResourceChanges(zeitsteineChange: -1),
                shuffeldAnswerOptions: $weiterbildungsOptions),
        );
    }
}
