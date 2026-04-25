# Architektur & Kostenkalkulation: Georedundantes Setup (Hetzner)

## 1. Systemarchitektur-Skizze

```text
=========================================================================================
EBENE 1: GLOBALER TRAFFIC-VERTEILER (DNS FAILOVER)
-----------------------------------------------------------------------------------------
Anbieter: z.B. Cloudflare, AWS Route53 (NICHT das Standard Hetzner DNS)

                                     [ INTERNET USER ]
                                            |
                                            V
                             Anfrage an: www.deine-domain.de
                                            |
                              +-------------+-------------+
                              | GLOBAL DNS LOAD BALANCER  | (Führt permanente
                              | (mit Health Checks)       |  Erreichbarkeits-Tests
                              +------+-------------+------+  auf Port 443 durch)
                                     |             :
             (Route A - Normalbetrieb) |             : (Route B - Failover)
                                     V             :
=========================================================:================================
EBENE 2: APPLIKATION (HETZNER CLOUD)                    :
---------------------------------------------------------:-------------------------------
STANDORT 1: FALKENSTEIN (DE)                             : STANDORT 2: HELSINKI (FI)
=====================================                    : ==============================
                                     |                    :
[ÖFFENTLICHES NETZ]                  |                    : [ÖFFENTLICHES NETZ]
      (Hetzner Firewall A)           |                    :       (Hetzner Firewall B)
      Erlaubt: Port 80, 443          |                    :       Erlaubt: Port 80, 443
                                     |                    :
              V                      |                    :               V
+-------------+-------------+        |                    : +-------------+-------------+
|   VM 1: GATEWAY PROXY     |        |                    : |  VM 1b: GATEWAY PROXY     |
|   (Debian + Nginx)        |        |                    : |  (Debian + Nginx)        |
+-------------+-------------+        |                    : +-------------+-------------+
              |                      |                    :               |
[PRIVATES NETZ 10.0.0.0/16]          |                    : [PRIVATES NETZ 10.0.0.0/16]
      (Kein öffentlicher Zugang)      |                    :       (Kein öffentlicher Zugang)
                                     |                    :
              V                      |                    :               V
+-------------+-------------+        |                    : +-------------+-------------+
|   VM 2: PROD APP          |        |                    : |  VM 2b: PROD APP          |
|   (Debian + Yii 3)        |        |                    : |  (Debian + Yii 3)        |
| [Lokale SQLite DB Datei]  |        |                    : | [Lokale SQLite DB Datei]  |
+-------------+-------------+        |                    : +-------------+-------------+
              |                      |                    :               |
              V                      |                    :               V
=========================================================================================
EBENE 3: DATEN & SPEICHER (STAGING & REPLIKATION)
-----------------------------------------------------------------------------------------

        (Dashed lines indicate continuous data synchronization/replication)

      /==============\                                             /==============\
     ( Hetzner S3 DE )  <.-.-.-. S3 Cross-Region Replication .-.-.> ( Hetzner S3 FI )
      \==============/           (Bilder, Assets, Backups)          \==============/
             ^                                                             ^
             | (Yii 3 liest/schreibt Assets)                               |
             |                                                             |
      [VM 2 PROD APP]                                               [VM 2b PROD APP]
             |                                                             |
             | (SQLite DB Datei Replikation, z.B. via Litestream)          |
              \.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-./

=========================================================================================
```

## 2. Monatliche Kostenschätzung (Hetzner & Externe Dienste)

| Komponente | Anzahl & Spezifikation | Geschätzte Kosten / Monat |
| :--- | :--- | :--- |
| **Global DNS & Failover** | z. B. AWS Route 53 (für DNS-Hosting + Health Checks + Failover-Regeln) | ca. 3,00 € - 5,00 € |
| **VM 1 & 1b: Gateway Proxy** | 2x Hetzner Cloud (z.B. CX22/CAX11: 2 vCPU, 4 GB RAM) in DE und FI | ca. 7,60 € (2x 3,80 €) |
| **VM 2 & 2b: Prod App** | 2x Hetzner Cloud (z.B. CPX21: 3 vCPU, 4 GB RAM, 40 GB NVMe) in DE und FI | ca. 15,10 € (2x 7,55 €) |
| **VM 3: Test App** | 1x Hetzner Cloud in DE (hier reicht ein Standort völlig aus) | ca. 3,80 € |
| **IPv4-Adressen** | 3x öffentliche IPv4-Adressen separat (ca. 0,60 €/IP) | ca. 1,80 € |
| **Automatisierte Backups** | 20 % Aufschlag auf den VM-Preis für tägliche Backups (Prod & Proxy) | ca. 4,50 € |
| **Hetzner S3 Storage** | 2 Buckets (DE & FI) mit insgesamt ca. 100 GB Speicherplatz + API-Zugriff | ca. 2,00 € |
| **Traffic / Bandbreite** | Inklusiv-Volumen bei Hetzner (20 TB pro VM) | 0,00 € |
| **Netzwerk & Firewalls** | Privates Netzwerk und Cloud Firewalls (kostenlos bei Hetzner) | 0,00 € |
| --- | --- | --- |
| **GESAMT (Schätzung)** | **Vollwertiges, georedundantes High-Availability-Setup** | **ca. 37,80 € / Monat** |

## 3. Strategische & Operative To-Dos (Die "versteckten" Kosten)

Die finanziellen Kosten dieses Setups sind extrem gering. Der wahre Aufwand liegt in der **Einrichtung und Wartung** der Daten-Synchronisation:

* **SQLite-Replikation:** Einrichtung von Tools wie *Litestream* oder *LiteFS*, um Datenbank-Schreibvorgänge (z. B. neue Newsletter-Anmeldungen) in Echtzeit oder asynchron von DE nach FI zu spiegeln.
* **S3-Synchronisation:** Die Applikation (Yii 3) oder ein Hintergrund-Job muss sicherstellen, dass Uploads in beide regionalen Buckets gespiegelt werden.
* **CI/CD Deployment:** Git-Hooks oder Pipelines müssen Code-Änderungen parallel und sicher auf beide Prod-VMs (in Falkenstein und Helsinki) ausrollen.