# Go-Live eines Neos Projekts von extern zu unserem Standard-Hetzner Setup

Referenz-Ticket, aus dem der Inhalt erstellt wurde: https://gitlab.sandstorm.de/dsc-1898-ev/dsc-relaunch-2023/-/issues/114

## Vorbereitung (lange vor Go-Live):
- [ ] Kunde nach Liste von im DNS konfigurierten Sub-Domains fragen (z.B. schwimmen.dsc1898.de)
- [ ] explizit nach mit `www` und ohne `www` fragen

## Vorbereitung (1 Tag vor Go-Live):
- [ ] Kunden-DNS time to live (ttl) runter stellen auf 300s (5min) für Hauptdomain und alle vom Relaunch betroffenen Sub-Domains
  - Warum? Damit bei der Umstellung des DNS auf die neue Hetzner-Produktiv-Domain morgen, innerhalb von 5 Minuten auf die neue IP-Adresse gezeigt wird
  - Die 5 Minuten sind die Cache-Zeit, die sich die DNS-Server die IP-Adresse zur Domain merken. Nach 5 Minuten fragen sie wieder an, ob die IP noch die gleiche ist.
  - Im normalen Betrieb reduzieren wir die Menge der DNS-Anfragen, indem die Antwort eine Stunde gültig ist, für die Umstellung verkürzen wir diese Zeit.
- Pull-Request für Prod-Deployment vorbereiten
  - [ ] Matomo Anpassungen vor dem Go-Live
      - [ ] in der [Matomo-UI](https://matomo.dsc1898.sandstorm.de/index.php?module=CoreAdminHome&action=generalSettings&idSite=1&period=day&date=2023-06-01&activated=) neue Prod-Domain unter "Vertrauter Matomo Hostname" hinzufügen
      - [ ] in .env auf Prod-Server (`/home/deployment/deployments/dsc1898/.env`) den `MATOMO_HOST` auf neue Domain ändern
  - [ ] für SwiftMailer in der `app/Configuration/Production/Settings.yaml` den hostname/localdomain anpassen -> bzw. in env-Variable umwandeln und diese in `docker-compose-prod.yml` setzen
  - [ ] production.gitlab-ci.yml: A11y-Job anpassen (htaccess raus), aus Quality-Job die htaccess raus
  - [ ] im Caddyfile die URLs anpassen (prod-domain, ggf. matomo)
  - [ ] im Caddyfile htaccess entfernen
  - [ ] production.gitlab-ci.yaml Domains anpassen für zukünftige Prod-Deployments

## Go-Live
- [ ] wenn altes Prod System vorhanden: aktuellen Prod-Dump vom alten System ziehen und auf neuem Server einspielen
- [ ] Kunden-DNS switchen (prod-domain.de)
    - [ ] A-Record muss auf die IP [neue Hetzner IP] des neuen Servers (Hetzner) zeigen
    - !Downtime von DNS-Umstellung über Caddy Deployment bis Caddy Zertifikat geladen hat
- [ ] in den Gitlabvariables (Settings > CI/CD) für die `PROD_SERVER_SSH_KEY` Variable Hostname anpassen
- [ ] !!!Vorher muss DNS Switch passieren: neues Prod-Zertifikat holen (tls)
  - wenn Anwendung schon vorher deployed war, Caddy neustarten, damit er das neue Prod-Zertifikat holt
  - `docker compose caddy restart`

## Go-Live Verifikation
- [ ] Erreichbarkeit testen - sollte neues Neos auf Hetzner Server sein: prod-domain.de aufrufen -> läuft -> Neos Version prüfen (z.B. durch Login ins Neos)
- [ ] mit und ohne www testen, ggf. im Caddy nachziehen
- [ ] http und https testen
- [ ] Kontaktformular testen

## Go-Live Nachbereitung
- [ ] im Bitwarden den temporären Eintrag für sudo-Passwort von [temp-domain.dev] auf [prod-domain.de] umstellen, damit Bitwarden für Ansible das sudo Passwort findet
- [ ] im Ansible Repo [inventory.yml](https://gitlab.sandstorm.de/infrastructure/ansible-server/-/blob/main/mainframe/customers/[kunde]/inventory.yml) anpassen > Umstellung auf neuen Hostname und Ansible laufen lassen
    - [ ] Bitwarden CLI installieren `brew install bitwarden-cli`
    - [ ] Ansible laufen lassen: Ansible Repo lokal gecloned haben, eingerichtet (dev setup Skript)
    - [ ] im Kundenordner nach Bitwarden Passwort Aktualisierung `bw sync`
    - [ ] Bitwarden unlocken: `export BW_SESSION=$(bw unlock --raw)`
    - [ ] Ansible ausführen: `./run.sh -l prod-domain.de`
- [ ] Uptime Kuma anlegen
- [ ] NetData Monitoring aktivieren
- [ ] Temporäres Sandstorm-DNS (temp-domain.dev) und (matomo.temp-domain.dev) bei [Variomedia](https://my.variomedia.de) entfernen

### Go-Live Nachbereitung durch Kunden
- [ ] ca. 1h nach Switch DNS TTL wieder hoch auf 3600s (1h) stellen
- [ ] Reden mit [Kunde] wegen altem Hosting und wo Domain liegt -> damit bei Kündigung des alten Hostings nicht auch die Domains gekündigt werden -> das wäre sehr schlecht!
