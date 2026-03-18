# Plan: Refactor MoneyAmount HTML formatting into Blade component

## Context

`MoneyAmount` (domain value object in `app/src/`) has `format()` and `formatWithIcon()` methods that return HTML strings. This violates the hexagonal architecture — domain code must not contain presentation logic. We'll extract this into a Blade component and remove the HTML methods from the domain.

## Scope

- **~85 replacements** across ~20 Blade templates + 1 PHP file + test files
- Only `format()` and `formatWithIcon()` are affected — `formatWithoutHtml()` stays (used correctly in domain event descriptions)

## Steps

### 1. Create Blade component

**Create:** `app/resources/views/components/money-amount.blade.php`

- Accept `$value` (MoneyAmount object) and optional `$withIcon` boolean prop
- `format()` variant: `<span class='text--currency'>{{ number_format($value->value, 2, ',', '.') }} €</span>`
- `formatWithIcon()` variant: same HTML structure as current method (sign icon + euro icon + sr-only spans)
- Must produce **identical HTML output** to current methods (whitespace-exact) to avoid breaking Feature tests that assert on rendered HTML

### 2. Fix MoneySheetInsurancesForm special case

**File:** `app/app/Livewire/Forms/MoneySheetInsurancesForm.php` (line 23)

Currently stores pre-rendered HTML: `'annualCost' => ...->format()`. Change to store the raw `MoneyAmount` object or float value. Then update the template at `app/resources/views/components/gameboard/moneySheet/expenses/money-sheet-insurances.blade.php` (line 14) to use the new component.

### 3. Replace all `{!! ...->format() !!}` usages (~51 occurrences, ~17 files)

Replace with `<x-money-amount :value="..." />`

**Files:** sidebar.blade.php, lebensziel-phase-preview.blade.php, konjunkturphase-sell-investments-to-avoid-insolvenz-modal.blade.php, konjunkturphase-sell-immobilien-to-avoid-insolvenz-modal.blade.php, investitionen-sell-form.blade.php, investitionen-immobilien-sell.blade.php, investitionen-buy-form.blade.php, investitionen-type.blade.php, player-list.blade.php, money-sheet.blade.php, repay-loan-modal.blade.php, money-sheet-taxes.blade.php, kompetenzen-overview.blade.php, take-out-loan-modal.blade.php, money-sheet-loans.blade.php, money-sheet-living-costs.blade.php, money-sheet-investments.blade.php

### 4. Replace all `{!! ...->formatWithIcon() !!}` usages (~34 occurrences, ~13 files)

Replace with `<x-money-amount :value="..." with-icon />`

**Files:** resource-changes.blade.php, konjunkurphase-summary.blade.php (11 usages), konjunkturphase-sell-*-modal.blade.php, job-offers-modal.blade.php, money-sheet.blade.php (8 usages), money-sheet-investments.blade.php, investitionen-immobilien-buy.blade.php, money-sheet-salary.blade.php, lebensziel.blade.php, money-sheet-loans.blade.php, money-sheet-insurances.blade.php, investitionen-immobilien-sell.blade.php

### 5. Remove `format()` and `formatWithIcon()` from MoneyAmount

**File:** `app/src/Definitions/Card/ValueObject/MoneyAmount.php` — delete lines 59-63 and 71-84

### 6. Update tests

- **`app/tests/Unit/Definitions/Cards/MoneyAmountTest.php`**: Remove tests for `format()` (line 56-59) and `formatWithIcon()` (line 61-68)
- **`app/tests/Feature/Livewire/Views/Helpers/GameUiTester.php`**: 6 usages of `->format()` and 1 of `->formatWithIcon()` for building expected assertion strings. Replace with inline formatted strings using `number_format()` + the same HTML wrapper, or use `Blade::render()` to render the component.

### 7. Look for other HTML in Domain code

Exploration confirmed: **no other HTML-generating methods exist** in `app/src/` besides MoneyAmount. `formatWithoutHtml()` is clean plain text.

## Key risks

- **Whitespace sensitivity**: Feature tests assert on exact rendered HTML. The Blade component must produce byte-identical output to the old methods (watch for newlines/spaces in the `@if` blocks of the withIcon variant).
- **Livewire serialization** (Step 2): Storing MoneyAmount objects vs floats in the insurance form array — need to confirm Livewire handles the chosen approach.

## Verification

```bash
mise phpstan          # Verify no remaining references to removed methods
mise pest             # All tests pass
mise pint --test      # Code style check
```

Manual: visually verify money formatting in browser at money-sheet, konjunkturphase summary, investment modals, player list.
