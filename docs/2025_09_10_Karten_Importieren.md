# Karten importieren

Das hier dient als Dokumentation zum Importieren der Spielkarten. Die Karten werden von der Kundin in einem Excel
Spreadsheet gepflegt. Wir haben uns aus pragmatischen Gründen dafür entschieden, die Datei in CSV umzuwandeln und
per simplem PHP-Skript zu importieren. Die Karten werden als PHP-Code in den CardFinder geschrieben.

## Import Anleitung

- Excel Datei als CSV Exportieren -> eine CSV pro Tabelle
    - Achtung: prüfen, dass die Zeilen nicht umgebrochen werden (eine Zeile in Tabelle soll auch eine Zeile in der CSV sein)
- unter `/import/<tablename>.csv` ablegen
- in `/import/csv-importer.php` ganz unten die gewünschte Zeile aktivieren:
    ```injectablephp
    //importMiniJobCards();
    //importJobCards();
    //importWeiterbildungCards();
    //importKategorieCards();
    importEreignisCards();
    //importInvestitionenCards();
    //importKonjunkturphasen();
    ```
- in Verzeichnis `/import`: `php csv-importer.php | pbcopy` ausführen
    - in `app/src/Definitions/Card/CardFinder.php` an der richtigen Stelle einfügen
- Tests und phpstan laufen lassen (`dev pest --parallel` und `dev phpstan --pro`)

