## Änderung in Tabelle:
- Typ der Konjunkturphase (AUFSCHWUNG, BOOM, ...) als einzelne Spalte
- Trennung in Modifier und Auswirkungen
    - Modifier: Gehalt (bildet Bonuseinkommen ab), Kosten für Karten/Lebensunterhalt, besondere Modifier wie Kreditsperre oder Chance auf Rezession
    - Auswirkungen (auf z.B. Aktien/Kredite) -> Werte ohne Einheiten, Kommawerte mit Punkt statt Komma
- Ereignisse sind meist conditionalResourceChanges (oder Modifier)
    - Text/Beschreibung (für UI?)
    - prerequisite1 -> Voraussetzungen (HAS_JOB, HAS_CHILD, ...) analog zu Ereignissen
    - resourceChange1 => aus den möglichen ResourceChanges (guthabenChange, zeitsteineChange, bildungKompetenzsteinChange, freizeitKompetenzsteinChange, Lohnsonderzahlung, Extrazins oder Grundsteuer)
    - value1 => zugehöriger Wert
- Zeitsteine entweder festen Wert (Summe Zeitsteine) oder als conditionalResourceChanges -> keine extra Spalte notwendig
- Beschreibung von Ereignissen / conditionalResourceChanges -> soll die in der UI angezeigt werden? evtl. eher in der Beschreibung pflegen?


- 2x 2 Spalten Modifier
- 2x 4 Spalten ConditionalResourceChanges

## Begriffe

Eine Konjunkturphase wirkt über drei verschiedene Mechanismen auf das Spiel:

| Begriff | Was ist das? | Gilt für | Code |
|---|---|---|---|
| **Modifier** | Regel, die Berechnungen über Hooks verändert (z.B. Gehalt in %, Kosten für Karten/Lebenshaltungskosten in %, Kreditsperre, erhöhte Chance auf Rezession). Wird auch von Ereigniskarten verwendet. | einzelne Spielende, zeitlich begrenzt | `ModifierId`, `ModifierParameters`, `ModifierBuilder` |
| **Auswirkung** | globale Marktparameter der Konjunkturphase (Kreditzins, Dividende, Kursbonus für Aktien/Crypto/Immobilien) | alle Spielenden, während der Konjunkturphase | `AuswirkungDefinition`, `AuswirkungScopeEnum` |
| **ConditionalResourceChange** | einmalige Buchung zu Beginn der Konjunkturphase, wenn eine Voraussetzung erfüllt ist (z.B. Lohnsonderzahlung, Grundsteuer pro Immobilie, Extrazins pro Kredit) | einzelne Spielende, einmalig | `ConditionalResourceChange`, `StartKonjunkturphaseForPlayerAktion` |

Den Spielenden wird all das als **Auswirkungen** der Konjunkturphase angezeigt
(`KonjunkturphaseDefinition::getDisplayedAuswirkungen()` und `getDisplayedAuswirkungDescriptions()`):

- als Werte mit Tendenz: Gehalt, Lebenshaltungskosten, Kreditzins, Dividende
- als Texte: zusätzliche Modifier (z.B. Kreditsperre), die Beschreibungen der ConditionalResourceChanges und die
  Zeitsteine (Spalte "Zeitsteine, anzeigen als Verständnis ..." im Import)
- bewusst nicht angezeigt: der Kursbonus von Aktien, Crypto und Immobilien sowie die Kosten für Karten

Die Texte werden 1:1 aus dem Import übernommen und nicht aus den Werten berechnet. Sie müssen deshalb beim Pflegen der
Tabelle zu den Werten passen.
