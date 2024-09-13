# Analyzer für produktive Traces

Dies ist ein *Profiling Werkzeug*, zum Beantworten der Frage: "Was macht der produktive Server?"

- kann auf dem Produktivsystem *ohne Overhead* verwendet werden
    - Ist ein *sampling Profiler*, der alle Zeit X (typischerweise jede Millisekunde) einen Stack Trace schreibt.
- sollte immer nur einige Minuten zum Daten Sammeln aktiv sein, da hier schnell einige GB zusammen kommen können
- Die Daten werden heruntergeladen, dann kann man sie lokal auswerten.
- Es gibt verschiedene Auswerte-Scripte per SQL; und man muss vermutlich neue schreiben um detaillierte Erkenntnisse zu haben.

## Datenformat der Traces

die Traces sind in folgendem Format:

```
a;b;c;d;e 4
a;b;c;d;f 1
```

Dabei repräsentiert jede Zeile einen Stack trace von der Wurzel / Root-Funktion `a` bis zur zuletzt aufgerufenen Funktion `e` bzw `f`.

Die Zahl dahinter beschreibt, wie viele Sample-Intervalle lang (bspw. wieviele Millisekunden) sich dieser Stack Trace nicht geändert hat.

Nach der `prepare` Phase liegen die obrigen Daten in einem Parquet-File bzw. einer Clickhouse-Datenbank mit folgenden Spalten:

- `trace: Array[String]` - der Stack Trace
- `cnt: Int` - die Lände dieses Stack Traces (zu diesem Zeitpunkt).



## Überblick

```
┌─────────────────────────────────────┐ ┌───────────────────────────────────────────────────────────────────────────────────────────────────────┐
│               Server                │ │                                                 lokal                                                 │
                                                                                                                                                 
┌──────────────────────────┐                      ┌──────────────────────────┐             ┌──────────────────────────┐                          
│  Excimer PHP Extension:  │                      │                          │             │┌─────────────────────────┴┐      ┌─────────────────┐
│                          ├─────────────────────▶│    Konvertierung und     ├─────────────▶│Analyse / Aggregation mit ├─────▶│   Anzeige mit   │
│  low-overhead sampling   │                      │     Kompression (mit     │   parquet   ││  SQL / Clickhouse Local  │      │ speedscope.app  │
│         profiler         │ Download der Traces  │    clickhouse-local)     │    files    └┤                          │      │                 │
│                          │     (txt files)      │                          │              └──────────────────────────┘      └─────────────────┘
├──────────────────────────┤                      └──────────────────────────┘                                                                   
│         php.ini:         │                                                                                                                     
│  auto_prepend_file zum   │                                                                                                                     
│ Laden/Konfigurieren von  │                                                                                                                     
│         Excimer          │                                                                                                                     
└──────────────────────────┘                                                                                                                     
```

## Datensammlung auf Serverseite

### vpro (alter server)

ANSCHALTEN der Datensammlung:

```bash
ssh proserver@vpro0173.proserver.punkt.de

# delete all traces from the last collection
cd _traces
ls -lisah
rm *
cd .. 

vi auto_prepend_file.php
# now, comment-in the line "// startExcimer();".

# you do NOT need to restart anything, data collection DIRECTLY starts.
```

Danach einige Minuten warten, und Daten-Collection wieder anhalten (durch Einkommentieren der `startExcimer()` Funktion.

### hetzner (neuer Server)

ANSCHALTEN der Datensammlung

```shell
ssh -p29418 charly.oekokiste2024.sandstorm.de
cd /apps/global

docker compose exec -u0 php-fpm-82 bash
cd /apps/tracing

# delete all traces from the last collection
cd _traces/
ls -lisah
rm *
cd ..

vim auto_prepend_file.php
# now, comment-in the line "// startExcimer();".

rm ./traces/*
cp -R ./_traces/* ./traces

# you do NOT need to restart anything, data collection DIRECTLY starts.
```

Danach einige Minuten warten, und Daten-Collection wieder anhalten (durch Einkommentieren der `startExcimer()` Funktion.


## Daten herunterladen und analysieren

**Vorbereitung**

```bash
brew install clickhouse gomplate
```

Wenn du die Daten herunterlädst, musst du einen lokalen Namen angeben, unter dem die Daten abgelegt werden sollen. Wir
empfehlen das Format "MM_DD_HH:mm"; also bspw: "11_17_09:30" - sodass man weiß, wann die Daten erzeugt wurden.

```bash

# !!! WARNING - we got 2 servers
# To download from the second server
# export TRACE_TARGET=hetzner

dev download [name]
# example:
# dev download 11_17_09:30

# afterwards, prepare and concatenate the files to a single Parquet file for easier analysis
dev prepare [name]
# dev prepare 11_17_09:30

# **raw.sql**
# see the original data, without any processing (not very useful, see below).
dev analyze [name] raw.sql

# **cleaned.sql**
# MOST USEFUL starting point!
#
# because the original data shows data from different shops as *different* stack traces, it is useful
# to aggregate them across different shops. We do that by removing paths of different shops and replace
# them by "shops/". 
dev analyze [name] cleaned.sql

# **categorized.sql**
#
# further categorization of stack traces into different categories, which are shown at the beginning of the
# stack (at the top in the image).
dev analyze [name] categorized.sql
```


## Background, Recherche, Konfigurationsdetails

- Wir nutzen die Excimer PHP Extension zur Datensammlung: https://www.mediawiki.org/wiki/Excimer
    - eher unbekannt; aber funktioniert auch bei FreeBSD. Wird schon eine Weile verwendet. Der Sentry
      Profiler nutzt dies intern.
    - Alternativen: (und warum sie nicht infrage kommen)
        - https://github.com/adsr/phpspy: spannend, aber läuft nicht auf FreeBSD
        - https://github.com/reliforp/reli-prof: auch spannend, aber läuft auch nicht auf FreeBSD
        - spx: nicht für Produktiveinsatz gedacht
        - blackfire.io $: finde den Analyzer weird; eher nur Einzelprofiling anstatt continuous profiling
        - tideways.com $: läuft nicht auf FreeBSD, wäre sonst interessant
        - datadog $: läuft nicht auf FreeBSD, soll teuer sein

In der `php.ini` des Servers ist folgendes konfiguriert:

```
extension=/home/vpro0173/excimer/modules/excimer.so
auto_prepend_file=/var/www/auto_prepend_file.php
```

Das `auto_prepend_file.php` enthält:

```php
<?php

// !!!!!!!!!!!!! DO NOT DELETE THIS FILE !!!!!!!!!!!!!!!!!!!!
// This file is included via auto_prepend_file in php.ini - and it is used for
// profiling.
// !!!!!!!!!!!!! DO NOT DELETE THIS FILE !!!!!!!!!!!!!!!!!!!!

function startExcimer() {
        static $excimer;
        if (!class_exists(\ExcimerProfiler::class)) {
            // excimer.so profiling extension not loaded.
            return;
        }

        $excimer = new ExcimerProfiler();
        $excimer->setPeriod( 0.001 ); // 1ms
        $excimer->setEventType( EXCIMER_REAL ); // OR: EXCIMER_CPU, but does not work on FreeBSD.
        $excimer->start();
        register_shutdown_function( function () use ( $excimer ) {
                $excimer->stop();
                $data = $excimer->getLog()->formatCollapsed();
                file_put_contents('/var/www/_traces/' . getmypid(), $data, FILE_APPEND);
        } );
}

// HINT: to start PHP continuous profiling, comment-in the following line.
// startExcimer();
```

Now restart PHP-FPM via `/usr/local/etc/rc.d/php-fpm reload`

- [ ] TODO: excimer installiert
- [ ] TODO: auto-prepend-file deployed per Ansible; einschließlich php.ini
    - [ ] TODO: _traces Ordner angelegt per Ansible (mit .keep file)


