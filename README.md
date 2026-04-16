he project operates across three distinct environments with a specialized focus on data integrity for media assets:

* **Development (Dev):** Hosted locally on **macOS** (`/Users/markuswolff/t3.guru-wisdom.de`). This is the primary workstation and the "Source of Truth" for all high-resolution master media files.
* **Test & Production (Prod):** Both environments currently reside on a shared Linux-based system.
* **Media Assets:** The collection consists of approx. 441 MB of images (~411 MB originals). These are stored in `web/images/org` and are excluded from Git to prevent repository bloating.


## Prerequisites

- **PHP**: >= 7.4.0 (PHP 8.x recommended)
- **Composer**: For dependency management
- **FFmpeg**: Required for media processing scripts in `bin/`
- **msmtp**: Required on macOS for system notifications and error reporting via Google Workspace SMTP.
- **Web Server**: Nginx

## Installation

1. **Clone the repository:**
   git clone <repository-url>
   cd guru-wisdom.de

2. **Install dependencies:**
   composer install
   
3. **Initialize Environment:**
   The installation script will automatically set permissions for `runtime/` and `web/assets/` and generate a `cookieValidationKey` in `config/web.php`.

4. **Database Configuration:**
   Create a database and update the configuration in `config/db.php` (if not using the default SQLite/Mock setup).

5. **Start Development Server:**
   You can use the built-in PHP server provided by Yii:
   
   php yii serve
   
   Or use the provided `docker-compose.yml` or `Vagrantfile` for a containerized/virtualized environment.

## Directory Structure

/
├── assets/             # Application asset bundles
├── bin/                # Custom shell scripts for media/content processing
├── commands/           # Console commands (Yii CLI)
├── components/         # Custom application components and widgets
├── config/             # Application configuration files
├── controllers/        # Web controller classes (SiteController, DeployController)
├── helpers/            # Helper classes (GuruWisdom core logic)
├── models/             # Data models and form validation logic
├── runtime/            # Temporary files generated during runtime (logs, cache)
├── subproj/            # Subproj Allcountries.txt Cities with urd
├── tests/              # Codeception test suites (unit, functional, acceptance)
├── vendor/             # Composer dependencies
├── views/              # View templates (PHP/HTML)
├── web/                # Web root (CSS, JS, images, audio, video)
│   └── wisdoms/        # Markdown-based wisdom content
└── widgets/            # Custom UI widgets (Alert, VideoWidget)


## Configuration Details

### Web & Console Configuration

- **Web (`config/web.php`):** Includes `id`, `cookieValidationKey`, and components for `db`, `mailer`, `user`, and `urlManager`.
- **Console (`config/console.php`):** Used for CLI tasks, cron jobs, and the Integrity-Check.
- **Custom Parameters:** Global parameters are managed in `config/params.php`.


---


## Data Integrity & Self-Healing

To prevent "Bit Rot" and accidental corruption of images, a custom **Self-Healing** mechanism is implemented:

1. **Integrity Check:** A Yii2 Console Command (`integrity/check`) calculates SHA-256 hashes of all images and compares them against `config/hash_catalog.json`.
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


## Media & Content Processing

Specialized scripts located in `bin/`:

- `convertJpgMp3tomp4.sh`: Automates video creation from static images and audio.
- `genWisdomsMedia.sh`: General media generation for the wisdom platform.
- `headGenApplyHeader.zsh`: Utilities for managing file headers and IDs.


---


## Testing & Deployment

- **Testing:** Uses **Codeception**. Run all tests via `./vendor/bin/codecept run`.
- **Deployment:** A specialized `DeployController` and `actionDeploy` in `SiteController` facilitate automated updates via Git hooks or manual triggers.

---

## System Architecture 

[ Internet / User ]
                                │
                                ▼ (Port 443 / HTTPS)
                    ┌──────────────────────────┐
                    │      VM 1: Gateway       │
                    │   (Debian + Nginx Proxy) │  <-- SSL-Zertifikate, Firewall, Routing
                    └───────┬──────────┬───────┘
                            │          │
         Routing via        │          │       Routing via
   www.deine-domain.de      │          │     test.deine-domain.de
                            │          │
             ┌──────────────▼─┐      ┌─▼──────────────┐
             │ VM 2: Prod App │      │ VM 3: Test App │
             │  (Debian + PHP)│      │ (Debian + PHP) │
             │                │      │                │
             │   - Yii 3      │      │   - Yii 3      │
             │   - SQLite DB  │      │   - SQLite DB  │
             └───────┬────────┘      └────────┬───────┘
                     │                        │
                     └───────────┬────────────┘
                                 ▼ (API / HTTPS)
                       ┌──────────────────────┐
                       │  Hetzner S3 Storage  │ <-- Zwei Buckets: 1x Prod, 1x Test
                       └──────────────────────┘


---


## Project TODOs
### ✅ Completed
- [x] **Infrastructure:** 3-2-1 Strategy Design `[01.04.2026]`
- [x] **Automation:** Local macOS Setup (Launchd, Keychain, msmtp) `[01.04.2026]`
- [x] **Web Server:** Email Integration `[02.04.2026]`
- [x] **Feature:** Newsletter Function `[02.04.2026]`
- [x] **CLI:** Gemini Implementation `[02.04.2026]`
- [x] **Git:** Gemini Hook Implementation `[02.04.2026]`
- [x] **Content:** Migrate original images to high-resolution JPGs `[03.04.2026]`

### 📋 Open Tasks (Unprioritized)

**Backend & Storage**
- [ ] **Storage:** Implement Hetzner S3 Integration & `IntegrityController` (Self-Healing)
- [ ] **Database:** Evaluate/Setup SQLite DB for website analytics
- [ ] **Metadata:** Create JSON files for metadata
- [ ] **Create Test VM:** New Test VM Yii3 
- [ ] **Create Prod VM :** New Prod VM Yii3
- [ ] **Create one Public Proxy:** For Test and Prod 


**Frontend & Structure**
- [ ] **Routing/Pages:** Set up "About me" as entry site
- [ ] **Routing/Pages:** Create overview site for all wisdoms

**Maintenance & Quality Assurance**
- [ ] **Git:** History Cleanup (remove legacy binary blobs from repo)
- [ ] **QA:** Perform comprehensive testing
- [ ] **Yi 3:** Prepare the Migration to Yii 3 
 
