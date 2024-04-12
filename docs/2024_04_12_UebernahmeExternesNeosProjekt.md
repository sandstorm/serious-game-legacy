# Übernahme eines externen Neos Projekt


## Abfolge / Grob

- Überblick über den Prod Server

- Ziel 1: Alle Ressourcen lokal haben, um Prod Stand lokal am Laufen zu haben
    - Quellcode organisieren
        - Git -> Überblick über Codebase
        - Abgleich "welcher Code ist auf dem Dev Server" (wenn vorhanden)
        - Abgleich "welcher Code ist auf dem Prod Server" (!!!)
            - Leute patchen gern Production

    - Datenbank
    - Persistent Resources

- Ziel 2: Lokale Infrastruktur "erschaffen", um Projekt lauffähig zu haben
    - Shortcut: von unserem Boilerplate starten (Dockerfiles etc übernehmen)
    - alte PHP Version switchen
    - alte DB Version ggf. switchen


# Überblick über den prod Server

- per SSH auf den Server eingeloggt; umgeschaut. ZIEL: Herausfinden, wo liegt das Produktive Neos?

```bash
echo "TEST" > Web/testing.php
# dann http://[website]/testing.php aufrufen
# -> wenn TEST angezeigt wird, wissen wir, dass wir auf dem richtigen Ordner etc. sind.
```
- nach dem Test die Datei wieder löschen! :)

- Manchmal mehrere Schritte zum Finden des Prod-Codes notwendig. Beispiel: Man loggt sich per SSH auf dem Dev-System ein; und da gibt es ein `./deploy_.....sh` script, wo eine andere SSH Verbindung aufgemacht wird.

## Prod PHP Info holen -> wichtig für Docker Setup Nachbau, um richtige Version zu haben

- im Prod Web Verzeichnis `Web/test.php` anlegen

```php
<?php

phpinfo();

```

- abrufen über HTTP (identity-sign.de/test.php); herunterladen; test.php löschen!

- in welchem Flow-Context läuft Production?
    - `cp Web/index.php Web/test.php`
    - editieren, und `var_dump($context);` einfügen gegen Ende
    - -> aufrufen, welcher Context?
    - -> Context: `Production`
- mit diesem Context holen wir uns die tatsächlichen Datenbank Credentials

```bash
FLOW_CONTEXT=Production ./flow configuration:show --path Neos.Flow.persistence.backendOptions

ERGEBNIS: identity_db2_neos
```



# Quellcode organisieren

- Was liegt tatsächlich im Git/Code vor?
    - Gesamte Distribution oder nur Site Package
    - ggf. über mehrere Git-Repos verteilt?
- **Ziel: eine Distribution in einem Git-Repo**
- lokal clonen
- alten Stand in separaten Branch "backupen"
  ```bash
  # branch erstellen
  git branch legacy-master
  # pushen
  git push origin legacy-master
  ```
- jetzt auf neuem Main weiterarbeiten
  ```bash
  # branch erstellen
  git checkout -b main
  ```
- wenn nur Site Package im Git: bestehendes Package in eigenen Ordner schieben, um fast leeren Ordner zur Integration in unser Neos Kickstart Boilerplate zu haben
- Kickstart als Zip runtergeladen
    - Dateien in Projekt verschieben
    - .-Dateien auf Kommandozeile in Projekt verschieben
        - `mv mit /.*`
- git commit mit allen Dateien aus Kickstart
- kickstart.sh
    - Vendor aus bestehendem Projekt (composer.json -> package-key erster Teil)
    - Package aus bestehendem Projekt (composer.json -> package-key zweiter Teil)
    - init new Repo -> nein
- git add . -> neue Dateien committen
- durch Kickstart generierte Ordner/Dateien in app/DistributionPackages/ löschen und durch bestehendes Projekt ersetzen

## Abgleich: welcher Code ist auf Dev / Prod; ggf. Persistent Resources organisieren

```bash
# dev system
rsync -avz -e 'ssh -p 222' devident@dedi4459.your-server.de:/usr/www/users/devident devident_dev
# prod system
rsync -avz -e 'ssh -p 222' identity@dedi4459.your-server.de:/usr/www/users/identity identity_prod

# !!! Hinweis: Anstecknadeln.de wird NICHT analysiert / aktualisiert. War aufgefallen, weil ein weiteres Deploy-Skript im Ordner lag.
```

## DB Dump + Resources mit Synco herunterladen

https://sandstorm.github.io/synco/#/?id=usage

```bash
# auf dem Server - im Neos Distribution Order, wo das ./flow Binary liegt (bspw. ~/public_html)

# !! hier den richtigen FLOW_CONTEXT verwenden.
# Wir verwenden "--all", um einen VOLLSTÄNDIGEN Dump zu bekommen
export FLOW_CONTEXT=Production
curl https://sandstorm.github.io/synco/serve | sh -s - --all
```

auf dem lokalen System `synco receive`, wie es auch angezeigt wird.

im DB Dump nach ganz oben nach DB Server Version schauen:

```
Server version	10.5.21-MariaDB-0+deb11u1
```

> ALTERNATIVE DB-WEGE:
>
> - Prod DB Credentials auf dem Server aus Neos/Flow auslesen, richtigen Kontext verwenden (den wir oben rausgefunden haben)
>
> ```
> FLOW_CONTEXT=Production ./flow configuration:show --path Neos.Flow.persistence.backendOptions
> ```
>
> - -> entweder auf dem Host selbst mysqldump (muss immer klappen, da es genauso connected wie Neos/Flow)
>
> ```bash
> mysqldump --host 127.0.0.1 --user identity_2 -p identity_db2_neos > /tmp/dump.sql
> # Passwort wird gefragt
> 
> # -> dann Dump per SCP herunterladen.
> ```
>
> - ODER über MYSQL Client (IntelliJ -> Database -> Connect)
    >     - !!! sollte eigentlich nicht gehen, DB aus dem Web sollte nicht erreichbar sein.

## Setup lauffähig machen

- DB dump in richtige Stelle kopieren app/ContentDump/Database.sql.gz
- Resources ebenfalls -> app/ContentDump/Resources.tar.gz
  ```bash
  # im Prod Resources Ordner (Data/Persistent/Resources) folgenden Befehl: 
  tar -czf Resources.tar.gz *
  ```
- docker-compose.yml
    - Dockerfile deployment/local-dev/neos/Dockerfile
        - php Image nahe Prod Version
        - php: major und minor PHP Version aus Prod, denn höchste Bugfix Version und höchste Base-OS Version (z.B. Buster)
        - Im Projekt: `FROM php:7.2.34-fpm-buster`
        - `docker compose build`
        - Fehler anschauen -> bspw. PHP Extension, die in älteren PHP Versionen noch nicht existieren -> auskommentieren z.B. ffi
        - `docker compose up -d`
        - `docker compose ps --all` -> ist der Neos gecrasht?
        - `docker compose logs neos`
- composer.json und composer.lock
    - Haupt- composer.json und composer.lock in app/ ist noch vom Kickstart
    - composer.json und composer.lock von Prod ins app/ Verzeichnis kopieren

- Fehler bei Composer:
  ```
  neos-1  |   Problem 1
  neos-1  |     - neos/composer-plugin is locked to version 2.0.1 and an update of this package was not requested.
  neos-1  |     - neos/composer-plugin 2.0.1 requires composer-plugin-api ^1.0.0 -> found composer-plugin-api[2.6.0] but it does not match the   constraint.
  neos-1  |   Problem 2
  neos-1  |     - ocramius/package-versions is locked to version 1.5.1 and an update of this package was not requested.
  neos-1  |     - ocramius/package-versions 1.5.1 requires composer-plugin-api ^1.0.0 -> found composer-plugin-api[2.6.0] but it does not match the constraint.
  neos-1  |   Problem 3
  neos-1  |     - ocramius/package-versions 1.5.1 requires composer-plugin-api ^1.0.0 -> found composer-plugin-api[2.6.0] but it does not match the constraint.
  neos-1  |     - ocramius/proxy-manager 2.2.3 requires ocramius/package-versions ^1.1.3 -> satisfiable by ocramius/package-versions[1.5.1].
  neos-1  |     - ocramius/proxy-manager is locked to version 2.2.3 and an update of this package was not requested.
  ```

  => Workaround: die 3 beteiligten Pakete mal versuchen zu updaten (auf dem Host-System; Docker läuft ja noch nicht):

  `composer update --ignore-platform-reqs neos/composer-plugin ocramius/package-versions ocramius/proxy-manager`

- Fehler bei Composer: **history was rewritten?**

  ```
    - Installing neos/flow (6.1.2): Cloning 7f3bb7a94a from cache
    7f3bb7a94a7b6b9bb97b3fb9d7115a8df00a4520 is gone (history was rewritten?)
    Install of neos/flow failed
    - Installing neos/fluid-adaptor (6.1.2): Cloning 54bc802e97 from cache
    54bc802e970819f40f1f4a879cf6dc993d447e9a is gone (history was rewritten?)
    Install of neos/fluid-adaptor failed
    - Installing neos/eel (6.1.2): Cloning 224f93855d from cache
    224f93855dfd718ea1d11fc6695424477417497d is gone (history was rewritten?)
    Install of neos/eel failed
    - Installing neos/neos (5.1.4): Cloning 13ba6dbd80 from cache
    13ba6dbd809afcc5c109e5b612e1a2567cb2fd8f is gone (history was rewritten?)
    Install of neos/neos failed
    - Installing neos/media (5.1.4): Cloning 3b681dc92d from cache
    3b681dc92d4e14105b00336fe13586dc1a5d3040 is gone (history was rewritten?)
    Install of neos/media failed
    - Installing neos/content-repository (5.1.4): Cloning 5140bdbd4f from cache
    5140bdbd4f7ae18b72c8319ca2d3ae4ebc798ea8 is gone (history was rewritten?)
    Install of neos/content-repository failed
    - Installing neos/media-browser (5.1.4): Cloning 93b926690b from cache
    93b926690b373c5a68d430f34d1ffa12abbe15e5 is gone (history was rewritten?)
    Install of neos/media-browser failed
    - Installing neos/nodetypes-columnlayouts (5.1.4): Cloning 8a642b9187 from cache
    8a642b918797b90b61700c396a2d8d0e7a54ae70 is gone (history was rewritten?)
    Install of neos/nodetypes-columnlayouts failed
    - Installing neos/nodetypes-assetlist (5.1.4): Cloning c596349fe6 from cache
    c596349fe60da53dfc8fed7f8e0732f65f811748 is gone (history was rewritten?)
    Install of neos/nodetypes-assetlist failed
  ```

  -> WORKAROUND: Composer update, darauf achten dass wir nur nen patch level update haben.
  `composer update neos/flow neos/fluid-adaptor neos/eel neos/neos neos/media neos/content-repository neos/media-browser neos/nodetypes-columnlayouts neos/nodetypes-assetlist --ignore-platform-reqs`

  ```bash
  - Upgrading neos/content-repository (5.1.4 5140bdb => 5.1.4 43a4a0e)
  - Upgrading neos/eel (6.1.2 => 6.1.19)
  - Upgrading neos/flow (6.1.2 => 6.1.18)
  - Upgrading neos/fluid-adaptor (6.1.2 => 6.1.11)
  - Upgrading neos/media (5.1.4 3b681dc => 5.1.4 e771410)
  - Upgrading neos/media-browser (5.1.4 93b9266 => 5.1.4 1f6fc33)
  - Upgrading neos/neos (5.1.4 13ba6db => 5.1.4 2b4ab69)
  - Upgrading neos/nodetypes-assetlist (5.1.4 c596349 => 5.1.4 fde5cbc)
  - Upgrading neos/nodetypes-columnlayouts (5.1.4 8a642b9 => 5.1.4 c9e85b4)
  ```

- Fehler im Container: PHP Code benötigt zu neue PHP-Version: `requires php ~8.1.0 || ~8.2.0 || ~8.3.0 -> your php version (7.2.34 ...`

```
neos-1  | + composer install
neos-1  | Installing dependencies from lock file (including require-dev)
neos-1  | Verifying lock file contents can be installed on current platform.
neos-1  | Your lock file does not contain a compatible set of packages. Please run composer update.
neos-1  |
neos-1  |   Problem 1
neos-1  |     - laminas/laminas-code is locked to version 4.13.0 and an update of this package was not requested.
neos-1  |     - laminas/laminas-code 4.13.0 requires php ~8.1.0 || ~8.2.0 || ~8.3.0 -> your php version (7.2.34; overridden via config.platform, same as actual) does not satisfy that requirement.
neos-1  |   Problem 2
neos-1  |     - ocramius/proxy-manager is locked to version 2.14.1 and an update of this package was not requested.
neos-1  |     - ocramius/proxy-manager 2.14.1 requires php ~8.0.0 -> your php version (7.2.34; overridden via config.platform, same as actual) does not satisfy that requirement.
neos-1  |   Problem 3
neos-1  |     - webimpress/safe-writer is locked to version 2.2.0 and an update of this package was not requested.
neos-1  |     - webimpress/safe-writer 2.2.0 requires php ^7.3 || ^8.0 -> your php version (7.2.34; overridden via config.platform, same as actual) does not satisfy that requirement.
neos-1  |   Problem 4
neos-1  |     - ocramius/proxy-manager 2.14.1 requires php ~8.0.0 -> your php version (7.2.34; overridden via config.platform, same as actual) does not satisfy that requirement.
neos-1  |     - doctrine/migrations v1.8.1 requires ocramius/proxy-manager ^1.0|^2.0 -> satisfiable by ocramius/proxy-manager[2.14.1].
neos-1  |     - doctrine/migrations is locked to version v1.8.1 and an update of this package was not requested.
neos-1  |
```

- FIX: in composer.json config.platform.php befüllen (auf ZIELVERSION; also EXAKTE Version im Docker Container)
- => ab jetzt können wir `composer update` verwenden OHNE `--ignore-platform-reqs` anzugeben
- `composer update laminas/laminas-code ocramius/proxy-manager webimpress/safe-writer ocramius/proxy-manager`
- ```
    Loading composer repositories with package information
    Updating dependencies
    Your requirements could not be resolved to an installable set of packages.

      Problem 1
        - phpdocumentor/reflection-docblock 5.0.0 requires ext-filter ^7.1 -> it has the wrong version installed (8.3.2).
                                                                      !!!!! ERROR IS HERE !!!!!!!!!
        - phpunit/phpunit 6.0.13 requires phpspec/prophecy ^1.7 -> satisfiable by phpspec/prophecy[v1.10.2].
        - phpspec/prophecy v1.10.2 requires phpdocumentor/reflection-docblock ^2.0|^3.0.2|^4.0|^5.0 -> satisfiable by phpdocumentor/reflection-docblock[5.0.0].
        - phpunit/phpunit is locked to version 6.0.13 and an update of this package was not requested.
    ```

  Fehler: die PHP Extension Version ist gleich wie die PHP Version. Muss in `config.platform.ext-filter` eingetragen werden.

- Composer.json Gesamt `platform`:
  ```
  {
      "config": {
          "vendor-dir": "Packages/Libraries",
          "bin-dir": "bin",
          "platform": {
              "php": "7.2.34",                 <---- THIS LINE
              "ext-filter": "7.2.34"           <---- THIS LINE
          },
          "allow-plugins": {
              "neos/composer-plugin": true
          }
      },
  ```

- `docker compose up -d` läuft durch.
    - DB Migration im Entrypoint läuft durch.
    - DB Dump wird in entrypoint importiert
    - Resources werden importiert
    - Bei folgenden Zeilen sind wir fertig:
      ```
      neos-1  | + nginx
      neos-1  | [01-Feb-2024 13:33:51] NOTICE: [pool www] 'user' directive is ignored when FPM is not running as root
      neos-1  | [01-Feb-2024 13:33:51] NOTICE: [pool www] 'group' directive is ignored when FPM is not running as root
      ```

# nächste Phase im Browser

- Web Browser: http://127.0.0.1:8081/
    - Invalid resource URI "resource://IDS.Responsive/Private/Fusion/Root.fusion": Package "IDS.Responsive" is not available.
    - Package fehlt noch: `docker compose exec neos ./flow package:list` -> keine Ausgabe für "IDS"
    - in root composer.json eingetragen als Dependency - im `require` Block mit dem exakten Namen aus der composer.json des Packages.
    - `composer update` -> FEHLER: Hat das Paket nicht gefunden, da `repositories` im ROOT composer.json gefehlt haben:

    ```
        "repositories": {
            "dev": {
                "type": "path",
                "url": "DistributionPackages/*"
            }
        },

    ```

- Bisschen was entfernt:
    - Behat etc. aus `require-dev` (dev dependencies), da Projekt keine Tests hat.
        - dann auch im `entrypoint.sh` das `./flow behat:setup` entfernt

- Seite lädt; sieht auch vernünftig aus
  - [ ] TODO Icon Problem
- NETWORK TAB -> nach Fehlern schauen
  - [x] aufgefallen: alle Ressourcen von PROD geladen. -> Base URL konfiguriert.
  `Settings.yaml` - baseUrl entfernt
  - 2x gefunden über Codesuche in IDE -> einmal in DistributionPackages, einmal in Packages/Sites - das ist das selbe File (Package ist symlinked). In der IDE kann man eines der beiden auf Excluded setzen. (Rechtsclick im File Listing -> Mark directory as ... -> Excluded)
    - TODO: Base URL nochmal entfernen nach Update

## Neos und PHP Update

### Schritt 1: Composer Dependencies aktuell und konsistent, Docker Container enthält neue Codebase und crasht nicht beim Starten

- [ ] Update Neos und PHP
    - PHP Version wieder hochdrehen auf PHP 8.2
        - Dockerfile Baseimage php:8.2.16-fpm-bookworm (aktuelles Debian) - Notiz: Debian Update ist potentiell more breaking als die PHP Version (wegen Packages, die ggf. nicht mehr da sind/funktionieren)
        - in composer.json in config -> platform PHP version auf selbe Version pinnen ("php": "8.2.16") und auch für Extensions z.B. "ext-filter": "8.2.16"
    - composer.json aktualisieren -> neos/neos ~8.3.0, neos/nodetypes, neos/neos-ui
        - `composer update`
        - Composer Fehler fixen
            - Entfernen von "nicht mehr benötigten" gepinnten Dependencies aus root composer.json -> z.B. neos/fusion-afx - da diese Dependency in Neos 8 vom Core bereits benötigt wird
            - update von Package Versionen, für die neuere - kompatible - Versionen bereitstehen (z.B. neos/seo, swiftmailer...)
            - Problem: gerdemann/recaptcha ist nicht mit Neos 8 kompatibel - auf Github schauen - gibt es neues Release? -> nein,
                - Option 1: zwar im Code aktualisiert, aber kein Release erstellt (in composer.json sichtbar) -> dev-[branch-name] dev-master als Version nutzbar
                - Option 2: Pull Request für Version-Compatibility existiert -> es gibt einen Fork, den wir statt des original Packages nutzen können (siehe unten)
                - Option 3: selber forken.

```json
{
     "repositories": {
         // Das hier haben wir immer drin
        "dev": {
            "type": "path",
            "url": "DistributionPackages/*"
        },
         
         // NEU! -> der Fork wird verwendet anstatt des Haupt-Repositories
        "recaptcha": {
            "type": "vcs",
            "url": "https://github.com/dlubitz/Gerdemann.ReCAPTCHA.git"
        }
    },
    "require": {
        // ...
        // -> anschauen, wie der Branchname im FORK ist, den wir nehmen wollen. (dev-[branchname])
        "gerdemann/recaptcha": "dev-neos-8",
    }
}
```

- `composer update` läuft durch - Hurra! :)  - hat composer.lock aktualisiert.
- Damit können wir Container neu bauen.
- Container bauen `dev start` / `docker compose build`
  -`docker composer up -d` -> läuft oder läuft nicht? (`docker compose ps --all`)
    - wenn es nicht läuft, docker compose logs -> checken, was gecrasht ist
    - läuft: localhost:8081 Seite anschauen -> Frontend wird mit Laden nicht fertig -> unklar, was los ist
    - Test: geht Neos Backend Login: localhost:8081/neos -> geht -> PHP, Flow, Datenbank, Neos scheinen grundsätzlich zu laufen
    - Login ins Backend: geht und ist schnell
    - Idee 1: Cache löschen (./flow cache:flush im Container) --> unwahrscheinlich, dass es hilft (wir haben ja gerade nen neuen Container gebaut)
    - Idee 2: wie debugge ich Neos/Flow jetzt sinnvoll?

## Exkurs: Debugging  Neos / Flow Frontend Rendering

**Vorbereitung**

- in docker-compose.yml alle Packages mounten, um in Neos und Flow ändern zu können

**Schritt 1: Kommen wir bis zum Frontend Controller?**

```yaml
# EINKOMMENTIEREN, danach docker compose up -d
volumes:
# mount all packages - enable VirtioFS in your docker host settings or comment out this line
- ./app/Packages/:/app/Packages/:cache
```
- Neos\Neos\Controller\Frontend\NodeController.php -> showAction()

    ```php
    public function showAction(NodeInterface $node = null)
    {
        die("JA");
    ```
    - wenn beim Aufruf von localhost:8081 jetzt das "JA" kommt, dann funktioniert Routing, Flow Bootstrap..
    - nächste Test: die("JA2") am Ende der showAction() -> "JA2" wird ausgegeben -> Controller läuft erfolgreich durch -> nächster Schritt: Fehler vielleicht im Rendering

wenn FE Controller unauffällig --> Schritt 2

**Schritt 2: Fusion Rendering debuggen**

app/Packages/Application/Neos.Fusion/Classes/Core/Runtime.php -> Methode `public function evaluate(string $fusionPath, ...)`

hier ganz oben nen echo rein machen:

```php
    public function evaluate(string $fusionPath, $contextObject = null, string $behaviorIfPathNotFound = self::BEHAVIOR_RETURNNULL)
    {
        echo $fusionPath;
        echo "\n";
```

- ACHTUNG - erzeugt EXTREM viel Ausgabe, daher idealerweise per CURL aufrufen (`curl http://127.0.0.1:8081`) - damit man das in ein File pipen kann oÄ
- wenn es nicht fertig wird, irgendwann abbrechen. (sollte innerhalb von 2-5 Sekunden MAX ein Ergebnis liefern.)
- Zeilen sehen aus wie:

```
root<Neos.Fusion:Case>/documentType<Neos.Fusion:Matcher>/element<IDS.Responsive:Root>/body<Neos.Fusion:Template>/content/seitenleiste1Vererbt<Neos.Neos:ContentCollectionRenderer>/itemRenderer<Neos.Neos:ContentCase>/default<Neos.Fusion:Matcher>/element<Neos.NodeTypes:Page>/body<Neos.Fusion:Template>/content/seitenleiste1Vererbt<Neos.Neos:ContentCollectionRenderer>/itemRenderer<Neos.Neos:ContentCase>/default<Neos.Fusion:Matcher>/element<Neos.NodeTypes:Page>/body<Neos.Fusion:Template>/content/seitenleiste1Vererbt<Neos.Neos:ContentCollectionRenderer>/itemRenderer<Neos.Neos:ContentCase>/default<Neos.Fusion:Matcher>/element<Neos.NodeTypes:Page>/body<Neos.Fusion:Template>/content/seitenleiste1Vererbt<Neos.Neos:ContentCollectionRenderer>/itemRenderer<Neos.Neos:ContentCase>/default<Neos.Fusion:Matcher>/element<Neos.NodeTypes:Page>/body<Neos.Fusion:Template>/footerProducts<Neos.Neos:Menu>/items<Neos.Neos:MenuItems>/itemUriRenderer<Neos.Neos:NodeUri>/format
```

- ist der "Rendering Stack Trace" - welche Fusion Objekte wurden hintereinander gerendert?
- den von HINTEN (vom Ende her) lesen, da dies das spezifischste ist.
- Dort nach "Endlosschleifen" oÄ schauen.
    - -> per Find - in unserem Fall haben wir nach `items<Neos.Neos:MenuItems>/itemUriRenderer`  gesucht - ca. 1 Mio treffer in der Ausgabe :)
- -> ANNAHME: Irgend ein Problem im Menü Rendering - vmtl. im Bereich footerProducts

- nach 10 Minuten und dem Ausbauen aller Menüs noch KEIN ergebnis.

**Schritt 2a: Alternativer Ansatz debugging Rendering**

- Root.fusion im Projekt auf ein Minimum eindampfen, schauen ob die Seite rendert.
- Schrittweise Elemente einkommentieren, bis der Fehler auftritt.
- Wenn man eine Stelle gefunden hat, nochmals gegen checken -> bei Originaldatei starten; NUR das fehlerhafte Element auskommentieren und checken ob es dann geht.
    - !! manchmal sind auch 2 oder mehr Elemente das Problem; das kann man somit eingrenzen.
    - welches Element ist es? -> Vergleich Live-Seite und Lokal für optischen Vergleich :)

## Neos Update grob fertigstellen

- Release Notes lesen (5.3 -> 8.3) https://docs.neos.io/api/upgrade-instructions
    - ab 5.3 sehr wenige Dinge, die kaputt gehen können. Prototype Generator wurde schon mit 5.3 deprecated; wenn dieser nicht genutzt ist, Update sehr einfach.

## Spezialitäten der Seite

- [x] Suche
    - [x] ./flow nodeindex:build
        - Memory Limit PHP erreicht!
        - -> zum Testen, ob er mit mehr Memory durch kommt: `php -d memory_limit=-1 ./flow nodeindex:build --workspace live`
    - [x] Flowpack.SearchPlugin:CanRender -> Neos.Fusion:CanRender austauschen (https://github.com/Flowpack/Flowpack.SearchPlugin/pull/52)
    - [ ] TODO: Wiederkehrendes Re-Indexing (Cron)
- [x] Mehrsprachigkeit
- [x] Mailversand
- [x] Neos Backend (https://gitlab.sandstorm.de/gedankengut/identity-sign-backup)
    - [x] neuer Content
    - [x] Content ändern & publish
    - [x] Seite hinzufügen
    - [x] Seite löschen
    - [x] Bild hochladen
        - Class "Imagine\Vips\Imagine" not found - Check the logs for details
        - TODO fix me
        - ffi im Dockerfile hinzugefügt (geht erst ab PHP 8 -> da wir vorher PHP 7 als Baseimage hatten)
        - "rokka/imagine-vips": "0.*", in composer.json hinzugefügt

- [x] Redirect Handling checken
    - zum Checken den Source Path eines Redirects aufgerufen (z.B. `ueber-uns-1.html` -> `127.0.0.1:8081/ueber-uns-1.html`)
    - Fehler: Neos "Page not Found"
    - Debugging über Neos.Redirect Package steps
        - Ergebnis: Redirect Package funktioniert, aber findet keinen Redirect
        - Redirects nochmal angeschaut und festgestellt, dass Origin domain "www.identity-sign.de" gesetzt ist -> mit /etc/hosts Eintrag (127.0.0.1 www.identity-sign.de) mit der Domain getestet: `www.identity-sign.de:8081/ueber-uns-1.html` -> Redirect funktioniert korrekt
- [x] 404 Seite aufrufen
    - [x] !! Fehler bei 404 Seite
    - [x] MOC.NotFound benutzt --> MOC.NotFound entfernt. -> 404 Handling wie im Kickstarter eingebaut
        - https://gitlab.sandstorm.de/infrastructure/neos-on-docker-kickstart/-/blob/main/app/DistributionPackages/MyVendor.AwesomeNeosProject/Resources/Private/Fusion/Resources/NotFound.fusion?ref_type=heads
        - https://gitlab.sandstorm.de/infrastructure/neos-on-docker-kickstart/-/blob/main/app/DistributionPackages/MyVendor.AwesomeNeosProject/Resources/Private/Fusion/Helpers/ExtractDimensions.fusion?ref_type=heads
        - ggf. Anpassung der Such-Expression für die 404-Seite `@context.notfoundDocument = ${q(site).context(.....).children('[uriPathSegment="404"]').get(0)}` -> suche das 1. Kind, was "404" als URL Pfadsegment hat.

- [x] ReCaptcha
    - schwer lokal zu testen
    - Kann man meistens testen, wenn man einen /etc/hosts Eintrag für die Prod Domain anlegt - bspw. `127.0.0.1 www.identity-sign.de`
    - Dann im Browser http://[prod-domain].de:8081 aufrufen -> landet auf lokalem Rechner.
    - !! wenn das noch nicht reicht, kann man einen lokalen Caddy für SSL Terminierung nutzen.


## Produktiv-Setup

- [x] Setup Hetzner Cloud
    - Kunde hat Projekt angelegt und technik@sandstorm.de als Admin hinzugefügt
    - in Ansible-server -> Mainframe -> customers... neuen Kundenordner vom Template anlegen
    - Readme befolgen :)
        - run.sh ausführen -> von jemandem, der in der playbook.yml in der common-Role in den sudoers aufgeführt ist
- DNS Eintrag für kundenprojekt.sandstorm.dev bei Variomedia anlegen
    - damit Let's Encrypt ein Zertifikat auf eine Domain ausstellen kann
    - nur relevant, wenn Projekt schon live ist und wir es übernehmen
- Ingress / Webserver / Caddy Configuration
    - in `ingress-caddy-proxy/docker-compose-prod.yml` die environment Variable `CADDY_DOMAIN_OVERRIDE` setzen
    - - `./ingress-caddy-proxy/Caddyfile` und `./ci/production-ingress.gitlab-ci.yml` Kundendomain für SSL Zertifikat bzw. Hostname für SSH-Zugriff setzen
    - nach diesem Schritt sollte das Caddy-Deployment funktionieren
- Application Deployment
    - folder and file permissions
    - .env file with Database password (muss da sein, bevor die Datenbank das erste mal started, sonst muss der db Ordner gelöscht werden)
    - ports (Caddy -> Neos:8081)

---- hier weiter machen ----

### Daten-Import

#### Upload der Resources

Lokal ausführen:

```bash 
scp -P29418 Resources.tar.gz [username]@identity-sign-prod.sandstorm.dev:~
```

**Resources auf dem Server and die richtige Stelle und Permissions schieben**

(auf dem Server)

```bash
sudo su -
# now we are root
cd /home/deploy/[appname]
mv /home/sebastian/Resources.tar.gz .

# mal Resources anschauen -> liegen die noch in nem Unterverzeichnis oder nicht?
tar -tzvf Resources.tar.gz
# (mit ctrl-c abbrechen)

cd app_Data_Persistent/Resources
tar -xzvf ../../Resources.tar.gz

# fix permissions ->
ls -lisah
total 72K
 646920 4.0K drwxrwxrwx 18 www-data  www-data 4.0K Apr 12 10:37 .<--- correct owner
 642133 4.0K drwxr-xr-x  4 www-data  root     4.0K Apr 10 21:39 ..
 894209 4.0K drwxr-xr-x 18 sebastian users    4.0K May 28  2018 0   <-- wrong owner
 895852 4.0K drwxr-xr-x 18 sebastian users    4.0K May 28  2018 1
 
# we need to change the owner:
chown -R www-data:www-data .

# now validate again:
ls -lisah
```
* im Docker Container die Resourcen publishen (auf dem Server im /home/deploy/project-name Ordner):
```bash
docker compose exec neos /bin/bash
./flow resource:publish
```
* Seite neu laden
* wenn die Resourcen nicht ankommen -> check network Tab in Browser -> auf 404 Fehler prüfen -> falsche BaseUri?
    * `./flow configuration:show` nach BaseUri suchen -> wird diese per Configuration gesetzt?
        * falls ja -> in environment Variable raustrennen (bspw `%env:FLOW_BASE_URI%`) und in docker-compose[-prod].yml setzen^

#### Datenbank Dump einspielen

* Verbindung zur Prod-Datenbank per SSH Port Forward in IntelliJ herstellen -> siehe Projekt Readme
* vorher prüfen, dass die Tabellen leer sind (nodedata bspw)
* alle Tabellen in der neos-Datenbank droppen
* Datenimport: Rechtsklick auf "neos" (DB Name) -> import/export -> Restore with mysql
    * -> aktuellen Prod Dump einspielen
    * danach in den Container gehen (auf dem Server im /home/deploy/project-name Ordner): `docker compose exec neos /bin/bash`, dann `./flow doctrine:migrate`
* Seite aufrufen -> Content müsste da sein (ggf. noch Ressourcen kaputt / ungestyled / ...)



# TODO

Code Check
- wie häufig kommt mode = 'uncached' vor

Synco soll Resources als resources.tar.gz anlegen und den DB dump als Database.sql.gz -> um mit unserem Kickstart kompatibel zu sein


3rd Party Requests - Datenschutz
* Typekit Fonts werden von 3rd Party geladen
* Google Maps

* Ansible-Server customer template Readme -> verlinken auf Caddy Prod Vorlage in Neos on Docker Kickstart

