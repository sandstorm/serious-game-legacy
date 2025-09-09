# Insolvenz

## Wann?

- Immer am Ende der Konjunkturphase
- ~~Spielerin hat/hätte nach CompleteMoneySheetForPlayer einen negativen Betrag auf dem Konto~~
- ~~->Automatische(?) "Pfändung" (muss noch vor CompleteMoneySheetForPlayer-Event passieren?)~~
- ~~CompleteMoneySheet nicht verfügbar~~
- nach CompleteMoneySheet in Übersicht (zusätzliche Buttons/Info) => kann nicht auf "fertig" klicken
- Optionen:
    - Kredit aufnehmen (wenn möglich)
    - Geldanlagen verkaufen
    - Versicherungen kündigen
- Geld ~~von Pfändung~~ reicht nicht? => Insolvenz

## ~~Pfändung~~ Geldanlagen verkaufen

- Versicherungen kündigen -> reicht das Geld jetzt?
    - Ja => CompleteMoneySheetForPlayer
    - Nein => Investitionen verkaufen?
- Investitionen verkaufen (Reihenfolge?)
    - Aktien/Etf/Crypto zum aktuellen Wert
    - Immobilien zu 80% des Werts
    - Reicht jetzt?
        - Ja => Complete...
        - Nein => Versicherungen kündigen?
- keine Versicherung und keine Geldanlagen und Geld reicht nicht? => Insolvenz
- sobald man wieder im plus ist, verschwinden die Optionen und der "ich bin fertig" Button ist wieder da

## Insolvenz

- Lebenshaltungskosten fest auf minValue (je nach Modifier)
- alle Kredite werden getilgt
- geht immer 3 Jahre (Schulden werden nicht getrackt), kann nicht eher beendet werden
- nach 3 Jahren zu Ende

## TODO

- [x] Shaping
- [ ] Warnung vor drohender Insolvenz (wenn vor Konjunkturphasenende absehbar -> negativer Kontostand/kein Einkommen und Kontostand < Fixkosten)
- [ ] Abfrage: Kreditaufnahme oder Verkauf von Geldanlagen (wenn möglich)
    - [x] Versicherungen kündigen (wenn möglich)
    - [x] Investitionen verkaufen (wenn möglich)
    - [ ] Immobilien verkaufen
- [ ] Insolvenz:
    - [x] Kontostand = 0 €
    - [ ] alle Kredite werden getilgt (nochmal abklären)
    - [ ] Unaffordable Ereigniskarten?
    - [x] ~~Modifier:~~ Lebenshaltungskosten auf minValue -> mit Validator
    - [x] ~~Modifier:~~ max 10.000 € (netto?) vom Job dürfen behalten werden -> über MoneySheetState + Insolvenzabgaben
    - [x] ~~Modifier:~~ Ereignisse, die Geldbeträge auszahlen, zahlen nur 50% aus -> EreignisCommandHandler
    - [x] ~~Modifier:~~ Kreditsperre -> mit Validator
    - [x] ~~Modifier:~~ Versicherungssperre -> mit Validator
    - [x] ~~Modifier:~~ Lebenshaltungskosten selbst zahlen, wenn möglich, sonst erstattet (komplett ~~oder nur Fehlbetrag?~~)
    - [x] Investitionen: Beschluss von Martin -> erstmal Investitionssperre -> mit Validator
- [x] Kreditvoraussetzungen nach Insolvenz -> ohne Job nur noch 50% des Vermögens (statt 80%)
- [ ] UI
    - [x] Kontostand in Übersicht (Konjunkturphasenwechsel)
    - [ ] Abgaben an Insolvenzverwalter explizit in Moneysheet ausweisen
    - [ ] Buttons
    - [ ] Erklärungstexte
    - [ ] Modals?

## Commands, Aktionen, Events, "State"

- nach CompleteMoneySheetForPlayer -> ist Kontostand positiv?
    - ja: alles fein, weiter wie bisher
    - nein: neue Optionen (siehe "Zahlungsunfähigkeit")
    - [x] MarkPlayerAsReady... braucht `hasPositiveBalanceValidator`
- "Zahlungsunfähigkeit":
    - [x] check: `hasPlayerGeldanlagen` und `hasPlayerInsurances`
        - ja -> Optionen zum Verkaufen/Kündigen
        - nein -> "Insolvenz Anmelden" als Option
- UI: Immer Kontostand mit sichtbar (und auch immer mit aktualisieren)
- Versicherungen Kündigen
    - [x] Refactor: Versicherung wird immer im Voraus für's Jahr bezahlt
    - [x] UI: Auflistung der Versicherungen + "Kündigen" Button
- Geldanlagen Verkaufen
    - [x] neue Aktion(en), Commands, Events (`sellInvestitionToAvoidInsolvenz`, ...)
    - [x] kein Einfluss auf Kurse
    - [x] kostet keinen Zeitstein/keine Slots
    - [x] kein Prompt für andere Spielende zum Verkaufen
    - [ ] Sonderfall Immobilien: 80% des (Einkaufs-?) Werts
    - [ ] "Alles Verkaufen" als Option?
    - [x] UI: Liste aller Geldanlagen + "Verkaufen" Butten (+ Input für Anzahl?)
- Wenn nix mehr zu verkaufen/kündigen
    - [x] `FileInsolvenzForPlayer`, `PlayerHasFiledForInsolvenz`
    - [x] "MarkPlayerAsReady..." sollte jetzt wieder verfügbar sein
    - [x] UI: Button "Insolvenz anmelden"

## Fragen

- Insolvenz automatisch erkennen?
    - nicht genug Geldanlagen -> automatisch Insolvenz anstoßen oder trotzdem Spielerin alles einzeln verkaufen lassen, bis der Button kommt
