# Flow Exception Logging zu NATS / Grafana

## ZIEL / Problem

Aktueller Zustand: Flow Exceptions werden auf den Servern geloggt, und wir bekommen es nicht mit. Dies hat Nebenwirkungen:
- Platz auf dem Server
- ggf. Degraded Service (es funktioniert was nicht, was funktionieren sollte) **Dies wollen wir proaktiv mitbekommen.**

**Wir wollen für ALLE Kundenprojekte, dass Flow Exceptions an eine zentrale Stelle geschickt werden, und dass wir eine
Übersicht bekommen. Incl. Alerts in Slack.**

## Lösungsskizze

```
┌────────────────────┐                                                         
│                    │                                                         
│Flow/Neos Anwendung │                                                         
│                    │                                                         
└────────────────────┘                                                         
   Data/Logs shared                                                            
      (auf Host)                                                               
┌────────────────────┐     ┌─────────────────────┐      ┌───────────────────────────────┐
│     vector.dev     │     │        NATS         │      │Zentraler Vector Aggregator,   │
│    sammelt Flow    │────▶│(natsv1.cloud.       │─────▶│  Clickhouse & Grafana         │
│   Exceptions ein   │     │ sandstorm.de)       │      │                               │
└────────────────────┘     └─────────────────────┘      └───────────────────────────────┘
```

## Detaillierte Implementierung

- vector.dev COLLECTOR:
  - konfiguriert über [vector.flow.yaml](../deployment/production/vector.flow.yaml)
  - teil der `docker-compose-prod.yml`
- NATS.io credentials kommen von `/home/deploy/vector-nats-logging.creds` und werden per Ansible (Rolle [nats_logging_credentials](https://gitlab.sandstorm.de/infrastructure/ansible-server/-/tree/main/mainframe/roles/nats_logging_credentials?ref_type=heads))
  auf das System gelegt (momentan für alle Projekte das selbe File).
  - dieses Credential hat in NATS.io den Scoped Signing Key(=Role) `logging_nats_customers`
    - Publish möglich in `logs.default.customer.>`

## Test des Loggings - Wiretapping NATS.io

- NATS admin anlegen:
  ```bash
  ./dev.sh admin-user
  # (select account SANDSTORM)
  # enter your Bitwarden password
  # you are in the correct NATS context to connect now, but if not: `nats context select ROOT_natsv1_SANDSTORM_admin`
  
  # Subscribe to Customer Logs:
  nats sub 'logs.default.customer.>'
  ```

## Test in Clickhouse - was steht in der Datenbank

[Wie connecte ich zu Clickhouse?](https://gitlab.sandstorm.de/infrastructure/k8s/-/tree/main/clickhouse?ref_type=heads#connecting-to-clickhouse-from-the-local-system)

## Alternativen / No Gos:

### Sentry

https://sandstorm-media.slack.com/archives/C0BS8E4G6/p1720520311561849?thread_ts=1720182273.771869&cid=C0BS8E4G6
```
das tldr:
Mittelfristig kann unser Logging und Monitoring alles was wir so brauchen (Logs, Exceptions, Traces, ...); wir müssen es aber hin und wieder erweitern und gerade im Bereich "gute Grafana Dashboards" haben wir noch größere Defizite. Einer der Hauptvorteile ist, dass wir mit SQL direkt auf die Rohdaten zugreifen können.
zusätzlicher Vorteil von unserem Logging: In Infrastruktur integriert, man muss Projekt in keiner Weise anfassen damit man davon profitieren kann.
Sentry könnte ne hilfreiche Lösung sein, wenn Leute sehr eigenverantwortlich Betriebsverantwortung für Projekte haben, da dies unabhängig von unseren Infrastruktur-Fortschritten genutzt/eingebaut werden kann; und viel im Bereich Exceptions "out of the Box" liefert.
Sentry hat zusätzliche hilfreiche Funktionen wie bspw. ein CSP Policy Violation Endpoint (den wollen wir mal testen).
Sentry ist KEINE allgemeine Log Aggregation.
Wir machen ein Experiment -
setzt ein Test-Sentry auf, was für Projekte genutzt werden kann (wird erstmal vrsl. in Traxpert, Filmfest eingebaut (die Projekte an denen Timon / Anselm gerade sind). Dann schauen wir mal, "wie viel Brot es frisst" im Betrieb, und auch wie einfach es sich updaten lässt etc.
=> Server ist auf Hetzner Cloud gemietet, Timon richtet ihn in nächster Zeit mal ein und meldet sich dann mit Details.
```
