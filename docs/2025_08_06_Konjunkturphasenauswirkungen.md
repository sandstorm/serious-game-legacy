# Auswirkungen der Konjunkturphasen

- 3 Varianten:
    - modifier (z.B. Lebenshaltungskosten, Gehalt, Immobilienpreise)
    - Werte, die in dem Event festgehalten werden (z.B. Anzahl Zeitsteine, Leitzins)
    - einmalige Zahlungen/Ereignisse

## Modifier - Gelten die gesamte Konjunkturphase

wird beim wechseln der Konjunkturphase abgehandelt -> KonjunkturphaseWasChanged implements ProvidesModifiers

- LebenshaltungskostenModifier -> additional multiplier for Lebenshaltungskosten
    ```injectablephp
        $lebenshaltungskosten = max([
            $gehalt * (Configuration::LEBENSHALTUNGSKOSTEN_MULTIPLIER + $lebenshaltungskostenMultiplierModifier) * $lebenshaltungskostenModifier,
            $modifiedMinValue,
        ])
    ```
- KostenBildungUndKarriere
- KostenSozialesUndFreizeit
- KostenImmobilien
- Gehalt
- Kreditsperre

## Soforteffekte - Werden von Spieler bestätigt?

wird in StartKonjunkturphaseForPlayer abgehandelt

- Lohnsonderzahlung
- Immobiliensteuern pro Objekt
- Grundsteuer pro Objekt
- Einmalige ResourceChanges (ohne Bedingung)

## Konjunkturphasen-properties - Werden nicht gesondert hervorgehoben? 

- Zins
- ZeitsteinSlots
- ZeitsteinePerPlayer
- Dividende
- Kursbonus (Aktien)
- Kursbonus (Crypto)


## Sammelliste Auswirkungen

- [x] MODIFIER: lebenshaltungskostenMultiplier (Modifier)
- [x] MODIFIER: bildungUndKarriereCostModifier
- [x] MODIFIER: sozialesUndFreizeitCosModifier
- [x] MODIFIER: bonuseinkommen -> gehaltModifier
- [x] MODIFIER: Kreditsperre - Modifier analog zu Investitionssperre
- [x] MODIFIER: 50% Chance, dass Rezession folgt -> Modifier
- [x] Kurzarbeit -> ConditionalResourceChange + Gehaltmodifier
    - [x] MODIFIER Gehaltmodifier
    - [x] CONDITIONALRESOURCECHANGE:
- [x] einmalige Gehaltssonderzahlung (1000 €) -> ConditionalResourceChange
- [x] einmalig 500 für gestiegene Lebensmittelpreise -> ConditionalResourceChange
- [ ] einmalige Lohnsonderzahlung (10%) -> TODO  (ConditionalResourceChange) -> Lohnsonderzahlung als Property
- [ ] für jede Immobilie fallen 1000 € (pro Objekt) an -> ConditionalResourceChange -> Grundsteuer als Property
- [x] 1 punkt in Bildung und Karriere -> ConditionalResourceChange
- [ ] Extrazins -> ConditionalResourceChange (brauchen neuen Prerequisite HAS_LOAN)
- [x] kreditZins (Definition)
- [x] aktienKursbonus (Definition)
- [x] cryptoKursbonus (Definition)
- [ ] etfKursbonus (Definition) ?
- [x] dividende (Definition)
- [ ] immobilien ??? (Definition)

Je 2 Spalten für Modifier (Modifier sind eigentlich selbst immer 2 Spalten) und ConditionalResourceChanges (sind je 4 spalten)

## alle Modifier 

- [x] Aussetzen
- [x] BerufsunfaehigkeitGehalt
- [x] BerufsunfaehigkeitJobsperre
- [x] BindZeitsteinForJob
- [x] Gehalt
- [x] Investitionssperre
- [x] LebenshaltungskostenKindMinValue
- [x] AdditionalLebenshaltungskostenKind
- [x] BildungUndKarriereCost
- [x] SozialesUndFreizeitCost
- [x] LebenshaltungskostenMultiplier (Konjunkturphase)
- [x] Kreditsperre
- [x] IncreasedChanceForRezession
