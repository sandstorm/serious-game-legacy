# Concept Components

## Problem — The raw idea, a use case, or something we’ve seen that motivates us to work on this

* Komponenten zu eng verzahnt
* zu viele Komponenten die bei Projektstart nicht oder nicht in dieser Form gebraucht werden
* beim Löschen muss man zu viele über Dateien verteilt suchen
* Ich hätte gern einen Bare-bone-template kickstarter für neue Komponenten (Bonus)
    * legt alle nötigen Dateien an (NodeType, Integrational, Presentational)
* Ich möchte on demand Komponenten hinzufügen
* Kickstarter-Weiterentwicklung muss weiterhin sinnvoll möglich sein
* Wir haben Basic Komponenten, die immer gebraucht werden, bspw Button, Image, Text, Headline
* Was passiert mit Utilities wie ContentWidth, BackgroundColor und TextAlign?
* Wie gehen wir mit Columns um?
* Wie erweitern wir Constraints? -> default der geht, alles andere Individualentwicklung
* Unterschiedliche Anforderungen an Menüs
    * kein sinnvoller Default möglich
    * Mobile menu extra 
    * evtl. 1-3 Menüs anbieten (Mega, default, Mini)
* Suche als Komponente?
    * wäre sick

## Appetite — How much time we want to spend and how that constrains the solution

* Retreat

## Solution — The core elements we came up with, presented in a form that’s easy for people to immediately understand

* base components sind standardmäßig installiert
* existierende Komponenten nachinstallieren, statt rausschmeißen
    * Build your component library: bsp: https://ui.shadcn.com/
* symlinks für die Entwicklung des Kickstarters → sieht für Neos so aus, als würde es alle Komponenten geben (Befehl im Dev-Runner)
* dev runner skripte
    * `dev list` für Auflistung aller verfügbaren Komponenten
    * `dev add "componentXyz"`
        * copy NodeType > file
        * copy Integrational > file
        * copy Presentational (dir)
    * `dev create component` zum erstellen einer Barbone Component
        * Fragt nach Namen und evtl. Type (Document evtl. erst mal ausklammern) 
    * `dev create repository` existierendes Skript einbinden

## Rabbit holes — Details about the solution worth calling out to avoid problems

* Was passiert mit der Initialisierung von AlpineJS Components beim hinzufügen von Komponenten?
    * Evtl. über Marker in der Main.ts lösen wo die Komponenten initialisiert werden
    * Alternative TODO Hinweis nach Hinzufügen der Komponente
    * Braucht es die Initialisierung überhaupt in der Main.ts? -> TODO Recherche
* Blog Komponente
  * erst mal komplett rausnehmen?
  * Blog Konzept?
  * Repository kickstarter nutzen?

## No-gos — Anything specifically excluded from the concept: functionality or use cases we intentionally aren’t covering to fit the appetite or make the problem tractable

* Updateablity von Komponenten im Nachhinein
* Neos-Kickstart an sich auch ins CLI-Tool reinziehen (raus aus der Clone-and-run-dev-Skript-Lösung wie bisher)
* Löschen ist nicht Teil des scripts

## UPDATE 2024.04.09 - 17:00

* MyVendor.AwesomeNeosProject wird umbenannt in Sandstorm.ComponentLibrary
  * kickstart.sh erzeugt komplett neues 2tes site package und installiert base components (nur neues site package muss geladen werden)
  * root composer.json erhält nur noch required libraries
  * composer.json im site package enthält alle custom libraries die von komponenten benötigt werden
  * library package bleibt bestehen, könnte bei prod deployment exkludiert werden (maybe docker ignore file)
