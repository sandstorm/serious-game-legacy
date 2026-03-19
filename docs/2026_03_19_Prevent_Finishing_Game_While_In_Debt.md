# Plan: Prevent players from finishing the game while in debt

## Context
Players can currently complete the game (finish Phase 3 of their Lebensziel) even while holding open loans. For a financial literacy game, this undermines the educational message. We need a validation that blocks game completion when the player has outstanding debt.

## Approach: TDD (Red → Green)

### Step 1: Write the red test
**File:** `app/tests/Unit/CoreGameLogic/Feature/Spielzug/SpielzugCommandHandlerTest.php`

Add a test inside the `describe('handleChangeLebenszielphase', ...)` block that:
1. Sets up 3 phase cards with generous resources (same pattern as the existing "finishes Lebensziel" test at line 1248)
2. Progresses the player through PHASE_1 → PHASE_2 → PHASE_3
3. Takes out a loan via `TakeOutALoanForPlayer::create($this->players[0], 10000)`
4. Activates the phase 3 card, then attempts `ChangeLebenszielphase`
5. Expects `RuntimeException` with message `'Cannot Change Lebensphase: Du kannst das Spiel nicht beenden, solange du noch offene Kredite hast'` and code `1751619852`

### Step 2: Create the validator
**New file:** `app/src/CoreGameLogic/Feature/Spielzug/Aktion/Validator/HasPlayerNoOpenLoansWhenFinishingValidator.php`

- Extends `AbstractValidator`
- Only checks when `currentPhase === LebenszielPhaseId::PHASE_3` (earlier transitions pass through)
- Uses `MoneySheetState::getOpenLoansForPlayer()` — if non-empty, returns failing `AktionValidationResult`
- Otherwise delegates to `parent::validate()`

### Step 3: Wire into the validation chain
**File:** `app/src/CoreGameLogic/Feature/Spielzug/Aktion/ChangeLebenszielphaseAktion.php`

Append `HasPlayerNoOpenLoansWhenFinishingValidator` to the end of the existing chain in `validate()`:
```
IsPlayersTurnValidator → DoesNotSkipTurnValidator → HasPlayerEnoughResourcesForLebenszielphasenChangeValidator → HasPlayerNoOpenLoansWhenFinishingValidator
```

## Verification
1. `mise pest tests/Unit/CoreGameLogic/Feature/Spielzug/SpielzugCommandHandlerTest.php --filter="open loans"` — new test passes
2. `mise pest` — no regressions (existing "finishes Lebensziel" test has no loans, so it still passes)
3. `mise phpstan` — static analysis clean
