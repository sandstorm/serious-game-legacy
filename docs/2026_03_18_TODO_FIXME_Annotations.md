# TODO & FIXME Annotations

Generated on 2026-03-18. This list covers source code annotations only (excluding vendor, compiled JS, and documentation about the phpstan-todo-by package).

---

## FIXME (high priority)

### 1. MoneySheetState: Gehalt calculation ignores modifiers

**File:** `app/src/CoreGameLogic/Feature/Moneysheet/State/MoneySheetState.php:112`

```php
private static function getEventsSinceLastGehaltChangeForPlayer(
    GameEvents $gameEvents,
    PlayerId $playerId
): GameEvents {
    // TODO We may need to change this later (e.g. quit job, modifiers)
    // FIXME this needs to change now with the modifiers
    $eventsAfterLastGehaltChange = $gameEvents->findAllAfterLastOrNullWhere(
        fn ($event) => $event instanceof JobOfferWasAccepted && $event->playerId->equals($playerId)
    );
    if ($eventsAfterLastGehaltChange === null) {
        $eventsAfterLastGehaltChange = $gameEvents->findAllAfterLastOfType(GameWasStarted::class);
    }
    return $eventsAfterLastGehaltChange;
}
```

The method only considers the last `JobOfferWasAccepted` event as a Gehalt change boundary. It does not account for salary modifiers (e.g. from Konjunkturphase or Ereignis cards) or quitting a job, leading to incorrect calculations when modifiers are active.

---

### 2. Excimer profiling broken with FrankenPHP

**File:** `deployment/laravel-root/tracing/auto_prepend_file.php:59`

```php
// HINT: to start/stop PHP continuous profiling, remove/add the comment in the following line.
// FIXME: This is currently not working with our franken-php-setup
// startExcimer();
```

The Excimer-based continuous profiling auto-prepend script cannot be enabled because it is incompatible with the FrankenPHP server setup. The `startExcimer()` call remains commented out.

---

## TODO — Core Game Logic

### ~~3. EreignisCommandHandler: Card matched by title string instead of ModifierId~~ FIXED 2026-03-18

**File:** `app/src/CoreGameLogic/Feature/Spielzug/EreignisCommandHandler.php:172`

Replaced fragile title string comparison (`=== "Geburt"`) with `ModifierId::LEBENSHALTUNGSKOSTEN_KIND_INCREASE` check, which all Geburt cards already carry in their `modifierIds`. This aligns with how every other event trigger in the same method works.

---

### 4. Grundsteuer calculation hardcodes 0 properties

**File:** `app/src/CoreGameLogic/Feature/Spielzug/Aktion/StartKonjunkturphaseForPlayerAktion.php:88`

```php
if ($conditionalResourceChange->isGrundsteuer) {
    $grundSteuerAmount = $conditionalResourceChange->resourceChanges->guthabenChange->value;
    $numberOfProperties = 0; // TODO use real number of Real Estate Properties once it's implemented
    return new ResourceChanges(guthabenChange: new MoneyAmount($grundSteuerAmount * $numberOfProperties));
}
```

Property tax (Grundsteuer) is always zero because the number of real estate properties is hardcoded to `0`. Needs to query the actual count once the real estate feature is implemented.

---

### 5. Direct command handler call instead of dispatching a command

**File:** `app/src/CoreGameLogic/Feature/Spielzug/Aktion/MarkPlayerAsReadyForKonjunkturphaseChangeAktion.php:68`

```php
if (KonjunkturphaseState::areAllPlayersMarkedAsReadyForKonjunkturphaseChange($gameEventsAndEventsToPersist)) {
    $konjunkturphaseCommandHandler = new KonjunkturphaseCommandHandler();
    return $eventsToPersist->withAppendedEvents(
        // TODO direct call of command handler
        ...$konjunkturphaseCommandHandler->handleChangeKonjunkturphase(
            ChangeKonjunkturphase::create(),
            $gameEvents
        )->events
    );
}
```

When all players are ready, the Konjunkturphase change is triggered by directly instantiating and calling another command handler. This bypasses the normal command dispatching flow and should be decoupled.

---

### 6. Insurance cancellation during "Aussetzen" — open question

**File:** `app/src/CoreGameLogic/Feature/Spielzug/Aktion/CancelInsuranceForPlayerAktion.php:36`

```php
return new AktionValidationResult(
    canExecute: true,
);
// TODO beim Aussetzen nicht erlaubt?
```

Open design question: should players be prevented from cancelling insurance when they are sitting out a turn ("Aussetzen")? Currently allowed unconditionally.

---

### 7. Resource validator untested with negative values

**File:** `app/src/CoreGameLogic/Feature/Spielzug/Aktion/Validator/HasPlayerEnoughResourcesValidator.php:15`

```php
/**
 * Succeeds if the player's current resources match or exceed the required resources.
 * TODO this might need some work; ResourceChanges have negative values for "required" resources;
 * I have not tested this yet
 */
final class HasPlayerEnoughResourcesValidator extends AbstractValidator
{
    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        if (!AktionsCalculator::forStream($gameEvents)->canPlayerAffordAction(
            $playerId,
            $this->resourceChanges
        )) { ... }
    }
}
```

The validator uses `ResourceChanges` where costs are represented as negative values. The sign-flipping logic in `canPlayerAffordAction` has not been thoroughly tested and may not handle all edge cases correctly.

---

### 8. AktionsCalculator: No structured result for failed requirements

**File:** `app/src/CoreGameLogic/Feature/Spielzug/State/AktionsCalculator.php:33`

```php
// TODO maybe return an object with failed requirements?
public function canPlayerAffordAction(PlayerId $playerId, ResourceChanges $cost): bool
{
    $playerResources = PlayerState::getResourcesForPlayer($this->stream, $playerId);
    if (
        ($cost->guthabenChange->equals(0) || $cost->guthabenChange->value * -1 <= $playerResources->guthabenChange->value) &&
        $cost->zeitsteineChange * -1 <= $playerResources->zeitsteineChange &&
        $cost->bildungKompetenzsteinChange * -1 <= $playerResources->bildungKompetenzsteinChange &&
        $cost->freizeitKompetenzsteinChange * -1 <= $playerResources->freizeitKompetenzsteinChange
    ) { ... }
}
```

Returns a plain `bool`. There is no way for callers to know *which* resource the player is missing. A structured result object would improve error messaging to players.

---

### 9. PlayerState::getGuthabenForPlayer — weak typing

**File:** `app/src/CoreGameLogic/Feature/Spielzug/State/PlayerState.php:126`

```php
public static function getGuthabenForPlayer(GameEvents $stream, PlayerId $playerId): MoneyAmount
{
    // TODO: wenig Typisierung hier -> gibt alles plain values etc zurück.
    $accumulatedResourceChangesForPlayer = $stream->findAllOfType(ProvidesResourceChanges::class)
        ->reduce(fn (
            ResourceChanges $accumulator,
            ProvidesResourceChanges $event
        ) => $accumulator->accumulate($event->getResourceChanges($playerId)), new ResourceChanges());

    return $accumulatedResourceChangesForPlayer->guthabenChange;
}
```

The accumulation pipeline works with mixed plain values and value objects. Should use proper value objects consistently throughout.

---

### 10. ModifierBuilder: Unhelpful RuntimeException message

**File:** `app/src/CoreGameLogic/Feature/Spielzug/Modifier/ModifierBuilder.php:38`

```php
return match ($modifierId) {
    ModifierId::GEHALT_CHANGE => [
        new GehaltModifier(
            playerTurn: $playerTurn,
            description: $description,
            year: $year,
            percentage: $modifierParameters->modifyGehaltPercent
                ?? throw new \RuntimeException("missing parameter"), // TODO better error message
        ),
    ],
```

When `modifyGehaltPercent` is null, the exception message is generic ("missing parameter") — should include the modifier ID and parameter name for easier debugging.

---

### 11. KonjunkturphaseWasChanged: PlayerTurn hardcoded to 0

**File:** `app/src/CoreGameLogic/Feature/Konjunkturphase/Event/KonjunkturphaseWasChanged.php:94`

```php
$modifiers = [
    ...$modifiers,
    ...ModifierBuilder::build(
        modifierId: $modifierId,
        playerId: $playerId,
        playerTurn: new PlayerTurn(0), // TODO make PlayerTurn optional
        year: $this->year,
        modifierParameters: $konjunkturphaseDefinition->getModifierParameters(),
        description: $konjunkturphaseDefinition->description,
    ),
];
```

`PlayerTurn(0)` is a dummy value. Konjunkturphase-level modifiers don't logically belong to a specific player turn, so `PlayerTurn` should be made optional in `ModifierBuilder::build()`.

---

### 12. KonjunkturphaseState: No guard against negative Zeitsteine total

**File:** `app/src/CoreGameLogic/Feature/Konjunkturphase/State/KonjunkturphaseState.php:36`

```php
// TODO we may need to safeguard against negative values at some point (probably not here though)
assert($totalNumberOfZeitsteine >= 0);
return $totalNumberOfZeitsteine === 0;
```

Uses `assert()` to check that total Zeitsteine across all players is non-negative. In production (assertions disabled), a negative total would silently pass. May need proper validation or an exception.

---

### 13. PreGameState: NameAndLebensziel DTO naming

**File:** `app/src/CoreGameLogic/Feature/Initialization/State/PreGameState.php:53`

```php
foreach (self::playerIds($gameEvents) as $playerId) {
    // TODO create new object with better naming and maybe different ones for different use cases
    $playerIdsToNameMap[$playerId->value] = new NameAndLebensziel(
        playerId: $playerId,
        name: PlayerState::getNameForPlayerOrNull($gameEvents, $playerId),
        lebensziel: PlayerState::getLebenszielDefinitionForPlayerOrNull($gameEvents, $playerId),
    );
}
```

`NameAndLebensziel` is used as a generic player-info DTO. The naming is misleading and it may need to be split into separate DTOs for different use cases (e.g. lobby display vs. in-game state).

---

### 14. GameEventStore: Event enrichment stubbed out

**File:** `app/src/CoreGameLogic/GameEventStore.php:61-62`

```php
private function enrichAndNormalizeEvents(GameEventsToPersist $events): Events
{
    // TODO: $initiatingUserId = $this->authProvider->getAuthenticatedUserId() ?? UserId::forSystemUser();
    // TODO: $initiatingTimestamp = $this->clock->now();

    return Events::fromArray($events->map(function (EventStore\DecoratedEvent|GameEventInterface $event) {
        return $this->eventNormalizer->normalize($event);
    }));
}
```

Events are not enriched with the initiating user ID or timestamp before persistence. This metadata would be useful for auditing and debugging but is currently commented out.

---

## TODO — Definitions

### 15. CardFinder: Placeholder card organization

**File:** `app/src/Definitions/Card/CardFinder.php:33`

```php
/**
 * TODO this is just a placeholder until we have a mechanism to organize our cards in piles (DB/files/?)
 */
final class CardFinder
{
    private array $cards;
    private static ?self $instance = null;
```

The entire `CardFinder` class is a placeholder. Cards are currently hardcoded; a proper storage mechanism (database or file-based) for organizing cards into piles is needed.

---

### 16. ModifierId::EMPTY — leftover enum case

**File:** `app/src/Definitions/Card/ValueObject/ModifierId.php:19`

```php
enum ModifierId: string
{
    case AUSSETZEN = 'Aussetzen';
    // ...
    case EMPTY = 'Leerer Modifier (TODO remove)';
    // ...
}
```

The `EMPTY` case is a placeholder that should be removed. Its presence may mask bugs where a modifier ID is required but not properly set.

---

### 17. ResourceChanges: MoneyAmount serialization inconsistency

**File:** `app/src/Definitions/Card/Dto/ResourceChanges.php:64`

```php
public function jsonSerialize(): array
{
    return [
        // TODO discuss, without jsonSerialize() MoneyAmount is an object in this array
        'guthabenChange' => $this->guthabenChange->jsonSerialize(),
        'zeitsteineChange' => $this->zeitsteineChange,
        'bildungKompetenzsteinChange' => $this->bildungKompetenzsteinChange,
        'freizeitKompetenzsteinChange' => $this->freizeitKompetenzsteinChange,
    ];
}
```

Without the explicit `->jsonSerialize()` call, `MoneyAmount` would be serialized as a nested object instead of a scalar value. This inconsistency between value types should be discussed and resolved (e.g. by implementing `JsonSerializable` on `MoneyAmount`).

---

### 18. InsuranceFinder: String-based ID instead of type enum

**File:** `app/src/Definitions/Insurance/InsuranceFinder.php:49`

```php
private static function initialize(): self
{
    self::$instance = new self([
        new InsuranceDefinition(
            // TODO refactor -> use type enum as id?
            id: InsuranceId::create(1),
            type: InsuranceTypeEnum::HAFTPFLICHT,
            description: 'Haftpflichtversicherung',
            annualCost: [ ... ]
        ),
    ]);
}
```

Insurance definitions use a numeric `InsuranceId` while also having an `InsuranceTypeEnum`. Since there's one definition per type, the type enum could serve as the ID, simplifying lookups.

---

### 19. InsuranceDefinition: Missing benefits/coverage field

**File:** `app/src/Definitions/Insurance/InsuranceDefinition.php:24`

```php
public function __construct(
    public InsuranceId       $id,
    public InsuranceTypeEnum $type,
    public string            $description,
    public array             $annualCost,
    // TODO add field for benefits or coverage details
) {
}
```

The insurance definition has no field describing what the insurance covers (e.g. payout amount, covered events). This information is needed for the game UI and rule enforcement.

---

## TODO — Tests

### 20. PileTest: Two tests marked as todo

**File:** `app/tests/Unit/CoreGameLogic/Feature/Pile/PileTest.php:81,89`

```php
// Test at line 81:
expect(PileState::topCardIdForPile($gameEvents, $this->pileIdBildungUndKarriere)->value)
    ->toBe($this->cardIdsBildung[count($this->cardIdsBildung) - 1]->value);
})->todo('fix or remove');

// Test at line 89:
expect($gameEvents->findLast(CardsWereShuffled::class)->piles)->toBeArray()
    ->and($gameEvents->findLast(CardsWereShuffled::class)->piles[0]->getCardIds())->toBeArray()
    ->and(count($gameEvents->findLast(CardsWereShuffled::class)->piles))->toBe(6);
})->todo('this needs to be refactored/rewritten/removed?');
```

Two pile-related tests are skipped via Pest's `->todo()`. The first tests top-card ordering after a Konjunkturphase change; the second tests that shuffling produces the expected pile structure. Both need to be fixed, rewritten, or removed.

---

### 21. MoneySheetStateTest: Disabled pending issue #445

**File:** `app/tests/Unit/CoreGameLogic/Feature/Moneysheet/State/MoneySheetStateTest.php:1087`

```php
expect(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0])->value)->toEqual($expectedGuthaben)
    ->and(MoneySheetState::getOpenRatesForLoan($gameEvents, $this->players[0], $loans[0]->loanId))->toEqual(0);
})->todo("Fix in #445");
```

Test verifying Guthaben and loan repayment state is disabled, waiting for a fix tracked in issue #445.

---

### 22. CardFinderTest: Missing salary reduction consequence

**File:** `app/tests/Unit/Definitions/Cards/CardFinderTest.php:129,209`

```php
resourceChanges: new ResourceChanges(
    guthabenChange: new MoneyAmount(-8000),
    bildungKompetenzsteinChange: +2,
    //TODO: Folgen -30% Gehalt einmalig (Option)
),
```

The "Weiterbildung zur Meisterin" card has a conditional consequence (one-time -30% salary reduction if the player has a job) that is not yet implemented in the resource changes or tested.

---

## TODO — Frontend / Views

### 23. Sidebar: Placeholder contact link

**File:** `app/resources/views/components/sidebar/sidebar-menu-modal.blade.php:17`

```html
<a href="#todo" target="_blank">Kontakt</a>
```

The "Kontakt" (contact) link in the sidebar menu points to `#todo` instead of a real URL. Needs to be updated with the actual contact page route.

---

## TODO — Import / Tooling

### 24. CSV importer: Possibly unnecessary additionalEvents output

**File:** `import/csv-importer.php:419`

```php
echo "\t" . "additionalEvents: '',\n"; //TODO remove?
```

The CSV importer for Konjunkturphase definitions outputs an empty `additionalEvents` field. This may be a leftover from an earlier data format and could be safe to remove.

---

## TODO — Configuration / Static Analysis

### 25. disallowed-calls.neon: `info()` helper status unclear

**File:** `app/disallowed-calls.neon:62`

```neon
- 'info()' # TODO: this might be OK
```

The Laravel `info()` helper is listed as a disallowed function call in PHPStan config, but there's uncertainty about whether it should actually be forbidden. `logger()` is explicitly allowed; `info()` is similar but writes at info level. Needs a team decision.

---

## TODO — Documentation (in architecture/code style docs)

### 26. ARCHITECTURE.md: Multiple placeholder sections

**File:** `ARCHITECTURE.md:99,113,141-142`

- Line 99: `TODO IMPLEMENT ME` — empty section awaiting content.
- Line 113: `TODO: wie nennt man das?` — naming question for the concept "Jahr = Gesamtspielzustand" (year as overall game state).
- Lines 141–142: `TODO: Describe` / `TODO: Ablauf"Datum"` — missing documentation for game flow and turn/date progression.

---

### 27. CODE_STYLE.md: Outdated/placeholder sections

**File:** `CODE_STYLE.md:135,194,222`

- Line 135: `TODO: Laravel Broadcast?` — open question about whether to use Laravel Broadcasting for real-time features.
- Line 194: `TODO: custom extension` — placeholder for documenting a planned custom IDE/tooling extension.
- Line 222: `OBSOLETE -- TODO UPDATE ME (based on Laravel IDEA)` — code style section is outdated and needs to be rewritten for the current tooling setup.
