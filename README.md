# 🧘‍♂️ guru-wisdom.de

Willkommen im offiziellen Repository von **guru-wisdom.de**. Dieses Projekt ist eine moderne, hochverfügbare und komplett zustandslose (stateless) Web-Plattform, die philosophische, spirituelle und historische Erkenntnisse ("Wisdoms") in einer performanten Architektur bereitstellt.

## 🚀 Über das Projekt

Die Plattform verzichtet bewusst auf eine klassische relationale Datenbank. Stattdessen folgt sie einem konsequenten **Content-as-Code**-Ansatz:
Alle Inhalte werden als Markdown-Dateien mit YAML-Frontmatter versioniert (`public/wisdoms/`). Die Applikation generiert daraus zur Laufzeit einen extrem schnellen, flüchtigen JSON-Cache. 

### 🛠️ Tech Stack
* **Framework:** [Yii3](https://github.com/yiisoft)
* **Webserver / PHP:** [FrankenPHP](https://frankenphp.dev/) (PHP 8.5)
* **Infrastruktur:** Docker, Hetzner Cloud, Cloudflare
* **Storage:** Georedundante Hetzner S3-Buckets

## 🏗️ Systemarchitektur

Das System ist auf maximale Ausfallsicherheit und Geo-Redundanz ausgelegt:

* **Edge / DNS:** Cloudflare dient als Loadbalancer, WAF und DDoS-Schutz.
* **Standorte:** Die Infrastruktur ist auf zwei Hetzner-Rechenzentren verteilt (Nürnberg & Helsinki).
* **Netzwerk-Segmentierung:** * `10.0.0.0/24`: Isoliertes Management-Netzwerk (Out-of-Band SSH, Administration).
  * `10.0.1.0/24`: Applikations-Netzwerk für den internen Traffic.
* **Server-Rollen:**
  * **Reverse Proxies** (`proxy10`, `proxy110`): NGINX-Gateways. Sie routen den Traffic per Stream (`ip_hash`) an die Docker-Hosts und fungieren als Media-Proxies für die S3-Buckets.
  * **Applikations-Hosts** (`prod30`, `prod130`, `test20`): Stateless Docker-Nodes, auf denen die eigentliche Applikation läuft.

## 🤖 Content Workflow (Das "Guru Wisdom" Gem)

Die Erstellung der Inhalte ist stark automatisiert und wird durch einen KI-Agenten (das "Guru Wisdom" Gem) unterstützt. 

Der Workflow funktioniert über definierte Trigger:
1. **Reflexion:** Dialogische Erarbeitung des Inhalts.
2. **Generierung:** Durch den Befehl `/wisdom` erzeugt das System ein komplettes Content-Paket:
   * Metaphorische Bildbeschreibung
   * Musik-Konzept / Track
   * Strukturiertes Markdown-Dokument (inkl. vorgegebenen Kategorien, Unified Tags und H1/H2-Logik)
   * Routing-URLs für die Umgebungen (Local, Staging, Live)

## 🔄 CI/CD & Deployment

Deployments erfolgen vollautomatisiert über **GitHub Actions**. Ein Push in den `main`-Branch löst folgende Pipeline aus:

1. **Test-Stage:** Ausführung der PHPUnit-Tests zur Sicherstellung der Code-Qualität.
2. **Staging:** Automatisches Deployment auf den Test-Server (`test20`).
3. **Manual Gate:** Manuelle Freigabe (Approval) durch den Administrator.
4. **Production & Replica:** Paralleles Deployment via SSH auf die produktiven Nodes in Nürnberg (`prod30`) und Helsinki (`prod130`).
   * *Mechanik:* `git pull` ➔ `docker compose up -d --build app` (Markdown wird ins Image gebacken) ➔ Cache-Invalidierung.

## 💻 Lokale Entwicklung

Voraussetzungen: [Docker](https://www.docker.com/) und [Docker Compose](https://docs.docker.com/compose/).

1. **Repository klonen:**
   git clone [https://github.com/MarkusWolffAix/guru-wisdom.de.git](https://github.com/MarkusWolffAix/guru-wisdom.de.git)
   cd guru-wisdom.de

2. **Umgebungsvariablen konfigurieren:**
   Kopiere die .env.example zu .env und passe die Werte für die lokale Entwicklung an.

3. **Container starten:**
   Wir nutzen eine dedizierte Compose-Datei für die Entwicklung.
   docker compose -f docker-compose.dev.yml up -d --build
   Cache leeren (falls nötig):

   rm -rf runtime/cache/*

Das Projekt ist nun lokal erreichbar. Die genauen Ports hängen von der .env-Konfiguration ab (standardmäßig Port 80/8080).


---


## Data Integrity & Self-Healing

To prevent "Bit Rot" and accidental corruption of images, a custom **Self-Healing** mechanism is implemented:

1. **Integrity Check:** A 
2. **Master Vault (Hetzner S3):** High-resolution originals are mirrored to a **Hetzner S3 Object Storage** bucket.
3. **Automatic Repair:** If a local file is found to be corrupt or missing, the system automatically pulls the original from Hetzner S3 and restores it.
4. **Reporting:** Failures trigger email alerts via `msmtp` using Google Workspace SMTP.


---


## Local Backup Strategy (3-2-1 Rule)

The macOS development environment is secured by an automated `zsh` script (`backup_guru_full.sh`) executed daily at **08:00 AM** via a macOS **Launch Agent**.

* **1. Primary Copy:** Local SSD on Mac. `/Users/markuswolff/Documents/Arbeit/Development/guru-wisdom.de`
* **2. Local Backup (Fritz!Box NAS):** Synchronizes the project to a USB drive on the Fritz!Box.
    * *Target Path:* `/Volumes/FRITZ.NAS/Volume/Backup/guru-wisdom-project`
    * *Network Intelligence:* Verifies the Router's **MAC Address** via `arp` before mounting.
    * *Security:* Credentials are retrieved from the **macOS Keychain** (`security find-generic-password`).
* **3. Cloud Backup (OneDrive):** Synchronizes to the local `OneDrive-Persönlich` folder for off-site redundancy.
    * *Target Path:* `/Users/marksuswolff/Library/CloudStorage/OneDrive-Persönlich/Backup/guru-wisdom-project`
    * *Target Path opt.*  `/Users/markuswolff/Library/CloudStorage/GoogleDrive-mwolff77@googlemail.com (Free 30/100G)`
---



## Testing & Deployment



## Project TODOs
### ✅ Completed
- [x] **Infrastructure:** 3-2-1 Strategy Design `[01.04.2026]`
- [x] **Automation:** Local macOS Setup (Launchd, Keychain, msmtp) `[01.04.2026]`
- [x] **Web Server:** Email Integration `[02.04.2026]`
- [x] **Feature:** Newsletter Function `[02.04.2026]`
- [x] **CLI:** Gemini Implementation `[02.04.2026]`
- [x] **Git:** Gemini Hook Implementation `[02.04.2026]`
- [x] **Content:** Migrate original images to high-resolution JPGs `[03.04.2026]`
- [X] **Create Test VM:** New Test VM Yii3 
- [X] **Create Prod VM :** New Prod VM Yii3
- [X] **Create one Public Proxy:** For Test and Prod 
- [X] **Git:** History Cleanup (remove legacy binary blobs from repo)
- [X] **QA:** Perform comprehensive testing
- [X] **Yi 3:** Migration to Yii 3 
- [X] **Routing/Pages:** Create overview site for all wisdoms

 

### 📋 Open Tasks (Unprioritized)

**Backend & Storage**
- [ ] **Storage:** Implement Hetzner S3 Integration & `IntegrityController` (Self-Healing)

**Frontend & Structure**
- [ ] **Routing/Pages:** Set up "About me" as entry site

