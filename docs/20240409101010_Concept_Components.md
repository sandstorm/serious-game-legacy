# Concept Components

## Problem — The raw idea, a use case, or something we’ve seen that motivates us to work on this

* Komponenten zu eng verzahnt
* zu viele Komponenten die bei Projektstart nicht oder nicht in dieser Form gebraucht werden
* beim Löschen 
* Kickstarter > Komponenten rausschmeißen, mehrere Dateien, Zusammenhänge
* Bare bone Template für neue Komponenten
* Was soll Kickstarter sein:
    * default Neos Setup
    * Application/Komponenten am besten on demand hinzufügen
* Kickstarter-Weiterentwicklung muss weiterhin sinnvoll möglich sein
* Wir haben Komponenten, die immer gebraucht werden, bspw Button, Image, Text, Headline
* Utilities wie ContentWidth drin bleiben

## Appetite — How much time we want to spend and how that constrains the solution

* Setup
* existierende Komponenten nachinstallieren, statt rausschmeißen

## Solution — The core elements we came up with, presented in a form that’s easy for people to immediately understand



* bsp: https://ui.shadcn.com/
* symlinks für die Entwicklung des Kickstarters → sieht für Neos so aus, als würde es alle Komponenten geben (Befehl im Dev-Runner)
* dev runner skripte
    * dev add Button
        * copy NodeType > file
        * copy Integrational > file
        * copy Presentational (dir)
    * dev create component —type document
    * 
* außerhalb app/ Ordner bspw. _internal
* list-Befehl für alle Komponenten

## Rabbit holes — Details about the solution worth calling out to avoid problems



## No-gos — Anything specifically excluded from the concept: functionality or use cases we intentionally aren’t covering to fit the appetite or make the problem tractable



* Updateablity von Komponenten im Nachhinein
* Neos-Kickstart an sich auch ins CLI-Tool reinziehen (raus aus der Clone-and-run-dev-Skript-Lösung wie bisher)
