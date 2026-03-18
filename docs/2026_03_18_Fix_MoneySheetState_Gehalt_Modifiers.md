# Fix TODO #1: MoneySheetState Gehalt calculation ignores modifiers

## Context

`MoneySheetState::getEventsSinceLastGehaltChangeForPlayer()` returns events since the last Gehalt change. It's used to count input "tries" for Steuern/Abgaben and Lebenshaltungskosten — the counter resets when Gehalt changes so the player re-submits values.

Currently it only recognizes `JobOfferWasAccepted` as a Gehalt boundary. It misses:
- `JobWasQuit` — Gehalt drops to 0/Lohnfortzahlung
- `EreignisWasTriggered` with `GEHALT_CHANGE` modifier — percentage salary change from event cards
- `KonjunkturphaseWasChanged` with `GEHALT_CHANGE` modifier — phase-based salary change

## Plan

### Step 1: Expand the filter in `getEventsSinceLastGehaltChangeForPlayer`

**File:** `app/src/CoreGameLogic/Feature/Moneysheet/State/MoneySheetState.php` (lines 107-120)

Replace the filter closure to match all four event types:

```php
private static function getEventsSinceLastGehaltChangeForPlayer(
    GameEvents $gameEvents,
    PlayerId $playerId
): GameEvents {
    $eventsAfterLastGehaltChange = $gameEvents->findAllAfterLastOrNullWhere(
        fn ($event) =>
            ($event instanceof JobOfferWasAccepted && $event->playerId->equals($playerId))
            || ($event instanceof JobWasQuit && $event->playerId->equals($playerId))
            || ($event instanceof ProvidesModifiers && self::eventProvidesGehaltModifier($event, $playerId))
    );
    if ($eventsAfterLastGehaltChange === null) {
        $eventsAfterLastGehaltChange = $gameEvents->findAllAfterLastOfType(GameWasStarted::class);
    }
    return $eventsAfterLastGehaltChange;
}
```

### Step 2: Add helper method `eventProvidesGehaltModifier`

Same file, add a private static helper:

```php
private static function eventProvidesGehaltModifier(ProvidesModifiers $event, PlayerId $playerId): bool
{
    foreach ($event->getModifiers($playerId)->getModifiers() as $modifier) {
        if ($modifier instanceof GehaltModifier) {
            return true;
        }
    }
    return false;
}
```

This reuses the existing `ProvidesModifiers` interface and `GehaltModifier` class. It naturally handles per-player filtering because `EreignisWasTriggered::getModifiers($playerId)` only returns modifiers for the matching player. `KonjunkturphaseWasChanged::getModifiers()` returns modifiers for all players, which is correct since phase changes affect everyone.

Note: `JobOfferWasAccepted` also implements `ProvidesModifiers` but only provides `BindZeitsteinForJobModifier` (not `GehaltModifier`), so the `ProvidesModifiers` check won't double-match it.

### Step 3: Add imports

Add to the imports in `MoneySheetState.php`:
- `use Domain\CoreGameLogic\Feature\Spielzug\Event\JobWasQuit;`
- `use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesModifiers;`
- `use Domain\CoreGameLogic\Feature\Spielzug\Modifier\GehaltModifier;`

### Files to modify

- `app/src/CoreGameLogic/Feature/Moneysheet/State/MoneySheetState.php` — the only file that needs changes

### Existing code to reuse

- `ProvidesModifiers` interface (`app/src/CoreGameLogic/Feature/Spielzug/Event/Behavior/ProvidesModifiers.php`)
- `GehaltModifier` class (`app/src/CoreGameLogic/Feature/Spielzug/Modifier/GehaltModifier.php`)
- `ModifierCollection::getModifiers()` (`app/src/CoreGameLogic/Feature/Spielzug/Modifier/ModifierCollection.php:27`)
- `JobWasQuit` event (`app/src/CoreGameLogic/Feature/Spielzug/Event/JobWasQuit.php`)

## Verification

1. Run existing MoneySheetState tests: `docker exec serious-game-legacy-laravel-1 php artisan test --filter=MoneySheetState`
2. Check the existing test at line ~267 ("resets tries when an event happens which changes the calculation") still passes
3. Run full test suite for regressions
