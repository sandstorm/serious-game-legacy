# Karten verwalten

Im Spiel gibt es folgende Karten:

* Kategoriekarten:
    * Bildung und Karriere
    * Soziales und Freizeit
* Jobs
* Immobilien (ETF, Crypto)/Investitionen
* Ereigniskarten
* Minijobs
* Weiterbildung

## Properties von Karten

### CardDefinition (alle Karten haben diese Felder + ihre eigenen)

* id
* title
* description
* category

### Kategoriekarten

* phase
* (year)
* ResourceChanges

### Jobs

* phase
* (year)
* requirements (Kompetenzsteine)
* Gehalt/annualPayment
* ResourceChanges (-Zeitstein)
* modifiers: (BIND_ZEITSTEIN)
    * modifierId
    * value

### Ereigniskarten

* (year)
* ResourceChanges (-Zeitstein)
* modifiers: (BIND_ZEITSTEIN)
    * modifierId
    * value
* prerequisites (cardId/job/kind/kein job/...)

### Immobilien

* ResourceChanges
* Income/annualPayment

### Minijobs

* ResourceChanges

### Weiterbildung

* question
* options
* correctAnswer


## Wie sehen die Properties aus?

Beschreibung folgt diesem Schema:

`<typ>`: <Beispiel>

### title

`string`: `"Kurze Kartenüberschrift"`

### description

`string`: `"Beschreibungstext für die Karte. Kann etwas länger sein"`

### category

`CategoryId`: `CategoryId::BILDUNG_UND_KARRIERE`

Kann im CSV auch gern nur `BILDUNG_UND_KARRIERE` sein.

### phase

`int`: `2`

### year

`int`: `2`

### resourceChanges

`ResourceChanges`:
```injectablephp
new ResourceChanges(
   guthabenChange: new MoneyAmount(200.0),
   zeitsteineChange:  -1,
   bildungKompetenzsteinChange: +1,
   freizeitKompetenzsteinChange: +1, 
)
```

Vorschlag für CSV (Trennzeichen kann z.B. `,` oder Zeilenumbruch sein):

```
guthaben: 200,
zeitsteine: -1,
bildungUndKarriere: +1
```

Es müssen nur die Felder angegeben werden, die sich auch ändern (Felder mit `0` können einfach weggelassen werden)

Alternativ: eine Spalte pro Feld (also je eine für `guthabenChange`, `zeitsteineChange`, `bildungsUndKarriereKompetenzsteineChange`,...)

### requirements (für jobs)

`JobRequirements`:
```injectablephp
new JobRequirements(
    bildungKompetenzsteine: 2,
    freizeitKompetenzsteine: 1,
)
```

Vorschlag für CSV:

zwei Spalten
```
bildungUndKarriere: 2,
sozialesUndFreizeit: 1,
```

### gehalt

`MoneyAmount`: `new MoneyAmount(44000.0)`
