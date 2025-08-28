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
