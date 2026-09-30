# Karten importieren

Das hier dient als Dokumentation zum Importieren der Spielkarten, Konjunkturphasen und Lebensziele. Die Daten werden
von der Kundin in Excel Spreadsheets gepflegt. Wir haben uns aus pragmatischen Gründen dafür entschieden, die Dateien in
CSV umzuwandeln und per simplem PHP-Skript zu importieren. Die Definitionen werden als PHP-Code in die Finder
geschrieben (`CardFinder`, `KonjunkturphaseFinder`, `LebenszielFinder`).

## Import Anleitung

- die xlsx Dateien unter `/import/` ablegen
- xlsx in CSV umwandeln (eine CSV pro Tabelle, die Zuordnung Tabelle -> CSV steht in `SHEET_NAME_PREFIX_TO_CSV_FILE`):
    ```shell
    python3 import/xlsx-to-csv.py import/KartenImportVorlage_final.xlsx import/Konjunkturen_import.xlsx import/Lebensziele_import_final.xlsx
    ```
    - Zeilenumbrüche in Zellen, leere Zeilen und Notizen neben/unter den Tabellen sind ok, der Importer ignoriert
      Zeilen ohne id und Spalten ohne Überschrift
- den PHP-Code für einen Typ erzeugen:
    ```shell
    php import/csv-importer.php <minijobs|jobs|weiterbildungen|kategorie|ereignisse|immobilien|konjunkturphasen|lebensziele> | pbcopy
    ```
    - Karten in `app/src/Definitions/Card/CardFinder.php` an der richtigen Stelle einfügen
    - Konjunkturphasen in `app/src/Definitions/Konjunkturphase/KonjunkturphaseFinder.php` einfügen
    - Lebensziele in `app/src/Definitions/Lebensziel/LebenszielFinder.php` einfügen
- Karten, die in der neuen Datei fehlen, nicht löschen, sondern nach `$legacyCards` im `CardFinder` verschieben.
  Alte Spiele referenzieren sie noch (Ansicht und JSON Export).
- `Configuration::DEFINITIONS_VERSION` erhöhen, damit Spiele, die mit den alten Definitionen gestartet wurden, nicht
  weitergespielt werden können
- Tests und phpstan laufen lassen (`mise pest` und `mise phpstan`)
