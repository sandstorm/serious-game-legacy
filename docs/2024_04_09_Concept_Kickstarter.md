# Kickstarter

## Problem — The raw idea, a use case, or something we’ve seen that motivates us to work on this

* wiederkehrende Neosprojekte in unserer Infrastruktur
* Unterschiedliche Coding Styles bei Entwickelnden
* wir wollen nicht jedes mal jede Komponente neu schreiben (Button, Image, etc)
* langsamer Projektstart für Neos-Projekte
* wir wollen gerne neu erlerntes Wissen wieder in neue Neos-Projekte einfließen lassen

## Appetite — How much time we want to spend and how that constrains the solution

* hoch

## Solution — The core elements we came up with, presented in a form that’s easy for people to immediately understand

* einheitliches Setup, welches sich in unsere Infrastrukur integriert (Local, Staging)
* aktuelles Neos/PHP mit vordefinierter Struktur
* Show Case zum Entwickeln (site import/export, menu)
* Nachinstallierbare Komponenten im [Kickstarter](./2024_04_09_Concept_Components)
* Keine fertige Seite

### Bisherige Lösung:
* Integration in Infrastruktur vorhanden
* aktuellste Neos & PHP Version
* Show Case vorhanden, aber aktuell auch immer beim Projektstart mit dabei
* kickstart.sh die das DistributionPackage umbenennt

### Notwendige Schritte:
* [Subkonzept Components](./2024_04_09_Concept_Components)
* kickstart.sh > mit oder ohne Show Case, welche Komponenten installieren
* Anchor rausschmeißen -> Package empfehlen (x)
* Folder Package -> empfehlen (x)
* Extra Readme mit Projekten die spannende Komponenten nutzen (x)

## Rabbit holes — Details about the solution worth calling out to avoid problems

## No-gos — Anything specifically excluded from the concept: functionality or use cases we intentionally aren’t covering to fit the appetite or make the problem tractable

* standardmäßig kein vordefiniertes Layout bzw. keine fertige Seite, die aus der Schachtel fällt

> in der Praxis hat sich gezeigt, dass wir vordefinierte Komponenten immer löschen mussten und dies hat eher zu Mehraufand gesorgt, als dass es eine Erleichterung war
