# Anleitung: Kurse und Spiele erstellen

Diese Anleitung beschreibt, wie Lehrpersonen eigene Kurse mit Spieler:innen anlegen und Spiele starten. Außerdem wird erklärt, wie Spieler:innen selbst Spiele erstellen und andere Personen einladen können.

**Voraussetzung:** Ein Admin hat bereits einen Account für die Lehrperson erstellt und die Zugangsdaten mitgeteilt.

## 1. Anmelden

1. Die App-URL im Browser öffnen.
2. Im Footer auf **"Login für Lehrpersonen"** klicken — dies führt zur Admin-Oberfläche (`/admin`).
3. Mit E-Mail und Passwort anmelden.

## 2. Spieler:innen anlegen

Es gibt zwei Varianten: manuell oder per CSV-Import. Bei der CSV-Variante werden die Spieler:innen erst nach dem Erstellen des Kurses importiert (siehe Abschnitt 4).

### Variante 1: Spieler:innen manuell erstellen

1. In der linken Navigation auf **"Spieler:innen"** klicken.
2. Oben rechts auf **"Erstellen"** klicken.
3. Formular ausfüllen:
   - **SoSciSurvey ID** — Die Kennung der Spielerin / des Spielers. Gedacht für die SoSciSurvey-ID, es kann aber auch ein beliebiger eindeutiger Benutzername verwendet werden (keine E-Mail-Validierung, muss keine E-Mail-Adresse sein).
   - **Passwort** — Ein Passwort für die Spielerin / den Spieler.
   - **Kann Spiele erstellen** — Optionaler Schalter. Erlaubt der Spielerin / dem Spieler, selbst Spiele zu erstellen.
4. Auf **"Erstellen"** klicken, oder auf **"Erstellen & weiterer Eintrag"**, um direkt die nächste Person anzulegen.
5. Schritte 2–4 für alle Spieler:innen wiederholen.

Anschließend weiter mit Abschnitt 3 (Kurs erstellen).

### Variante 2: CSV-Datei vorbereiten

Eine CSV-Datei mit Semikolon (`;`) als Trennzeichen und folgenden drei Spalten erstellen:

| SoSciSurveyId | Passwort | Kann Spiele erstellen |
|---|---|---|
| spieler1 | passwort123 | false |
| spieler2 | passwort456 | false |
| spieler3 | passwort789 | true |

Hinweise:
- **Trennzeichen ist Semikolon** (`;`), nicht Komma.
- **SoSciSurveyId** ist gedacht für SoSciSurvey-IDs, kann aber auch ein beliebiger eindeutiger Benutzername sein (keine E-Mail-Validierung).
- **Kann Spiele erstellen** wird als `true` oder `false` angegeben.
- Falls eine Spielerin / ein Spieler mit gleicher SoSciSurvey-ID bereits existiert, werden die Daten aktualisiert (kein Duplikat).

Der Import erfolgt nach dem Erstellen des Kurses (Abschnitt 4).

## 3. Kurs erstellen

1. In der linken Navigation auf **"Kurse"** klicken.
2. Oben rechts auf **"Erstellen"** klicken.
3. Formular ausfüllen:
   - **Name** — Ein Name für den Kurs (z.B. "Klasse 10a WS2026").
   - **Spieler:innen** — Bei Variante 1: hier die zuvor angelegten Spieler:innen auswählen. Bei Variante 2 (CSV-Import): leer lassen.
4. Auf **"Erstellen"** klicken.
5. Man wird automatisch auf die Kursdetailseite weitergeleitet.

## 4. Spieler:innen per CSV importieren (nur Variante 2)

Falls die Spieler:innen per CSV importiert werden sollen:

1. Auf der Kursdetailseite oben rechts auf **"Spieler:innen importieren"** klicken.
2. Die vorbereitete CSV-Datei auswählen.
3. Prüfen, ob die Spaltenzuordnung korrekt ist (SoSciSurveyId, Passwort, Kann Spiele erstellen).
4. Auf **"Importieren"** klicken.
5. Nach Abschluss des Imports die Seite neu laden.

Die importierten Spieler:innen sind nun dem Kurs zugeordnet.

## 5. Spiele erstellen

1. Auf der Kursdetailseite oben rechts auf **"Spiele erstellen"** klicken.
2. **Gruppengröße bevorzugen** auswählen:
   - *Bevorzugt 4er-Gruppen* (Standard)
   - *Bevorzugt 3er-Gruppen*
3. Bestätigen.

Die Spiele werden automatisch erstellt. Die Spieler:innen werden zufällig in Gruppen aufgeteilt.

**Hinweis:** Es werden mindestens 2 Spieler:innen benötigt.

### Wie funktioniert die Gruppenaufteilung?

Das System versucht, Gruppen der bevorzugten Größe zu bilden. Wenn die Anzahl der Spieler:innen nicht gleichmäßig aufgeht, werden die Gruppen angepasst — es entstehen keine Gruppen kleiner als 2 oder größer als 4.

Beispiele:

| Spieler:innen | Bevorzugte Größe | Ergebnis |
|---|---|---|
| 12 | 4 | 3 Gruppen mit je 4 |
| 11 | 4 | 2 Gruppen mit 4 + 1 Gruppe mit 3 |
| 9 | 4 | 3 Gruppen mit je 3 (da 2x4 eine Person allein lassen würde) |
| 10 | 3 | 2 Gruppen mit 3 + 1 Gruppe mit 4 |
| 5 | egal | 1 Gruppe mit 3 + 1 Gruppe mit 2 |

## 6. Spieler:innen treten dem Spiel bei

1. Spieler:innen öffnen die App-URL im Browser.
2. Anmelden mit **SoSciSurvey ID** und **Passwort**.
3. Das neu erstellte Spiel aus der Spielübersicht auswählen.
4. Jede Spielerin / jeder Spieler wählt einen Namen und ein Lebensziel.
5. Sobald alle Spieler:innen einer Gruppe bereit sind, startet das Spiel automatisch.

---

## 7. Spiele als Spieler:in erstellen (ohne Lehrperson)

Spieler:innen, bei denen die Option **"Kann Spiele erstellen"** aktiviert ist, können selbst Spiele erstellen und andere Personen per Link einladen. Die eingeladenen Personen benötigen keinen eigenen Account.

**Voraussetzung:** Die Option "Kann Spiele erstellen" wurde beim Anlegen der Spielerin / des Spielers aktiviert (durch die Lehrperson oder per CSV-Import mit `true` in der Spalte `Kann Spiele erstellen`).

### 7.1 Spiel erstellen

1. Die App-URL im Browser öffnen und mit **SoSciSurvey ID** und **Passwort** anmelden.
2. Auf der Spielübersicht auf **"Neues Spiel erstellen"** klicken.
3. **Anzahl der Spielenden wählen** — auf **2**, **3** oder **4** klicken.
4. Auf **"Weiter"** klicken.

### 7.2 Mitspieler:innen einladen

Nach dem Erstellen des Spiels wird eine Übersicht mit Links angezeigt:

- **Spieler 1 (Du):** Ein Button **"Spiel beitreten"** — hierüber tritt man selbst dem Spiel bei.
- **Spieler 2, 3, 4:** Jeweils ein Button **"Link teilen"** — dieser kopiert den Einladungslink in die Zwischenablage (oder öffnet auf Mobilgeräten die Teilen-Funktion des Betriebssystems).

**Wichtig:** Zuerst alle Links an die Mitspieler:innen weitergeben (z.B. per Messenger, E-Mail oder mündlich), **bevor** man selbst dem Spiel beitritt. Jeder Link ist einmalig und gehört zu genau einem Spielerplatz.

Die eingeladenen Personen benötigen keinen eigenen Account. Sie öffnen einfach den erhaltenen Link im Browser und werden direkt ins Spiel geleitet.

### 7.3 Spiel starten

1. Nachdem alle Links geteilt wurden, auf **"Spiel beitreten"** klicken (Spieler 1).
2. Die eingeladenen Mitspieler:innen öffnen ihren jeweiligen Link.
3. Jede Person wählt einen Namen und ein Lebensziel.
4. Sobald alle bereit sind, startet das Spiel automatisch.
