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

- [ ] Shaping
- [ ] Warnung vor drohender Insolvenz (wenn vor Konjunkturphasenende absehbar -> negativer Kontostand/kein Einkommen und Kontostand < Fixkosten)
- [ ] Abfrage: Kreditaufnahme oder Verkauf von Geldanlagen (wenn möglich)
- [ ] Insolvenz:
    - [ ] Kontostand = 0 €
    - [ ] alle Kredite werden getilgt (nochmal abklären)
    - [ ] Lebenshaltungskosten auf minValue
    - [ ] Unaffordable Ereigniskarten?
    - [ ] Modifier: max 10.000 € (netto?) vom Job dürfen behalten werden
    - [ ] Modifier: Ereignisse, die Geldbeträge auszahlen, zahlen nur 50% aus
    - [ ] Modifier: Kreditsperre
    - [ ] Modifier: Versicherungssperre
    - [ ] Lebenshaltungskosten selbst zahlen, wenn möglich, sonst erstattet (komplett oder nur Fehlbetrag?)
    - [ ] Investitionen: Beschluss von Martin -> erstmal Investitionssperre
- [ ] Kreditvoraussetzungen nach Insolvenz
- [ ] UI
    - [ ] Kontostand in Übersicht (Konjunkturphasenwechsel)
    - [ ] Abgaben an Insolvenzverwalter explizit in Moneysheet ausweisen
    - [ ] Buttons
    - [ ] Erklärungstexte
    - [ ] Modals?

