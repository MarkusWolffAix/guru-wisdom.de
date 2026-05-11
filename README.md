


## Prerequisites

- **PHP**: >= 8.5
- **Composer**: For dependency management
- **Web Server**: Nginx

## Installation

1. **Clone the repository:**
   git clone <repository-url>
   cd guru-wisdom.de




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

- **Testing:** Uses **Codeception**. Run all tests via `./vendor/bin/codecept run`.
- **Deployment:** A specialized `DeployController` and `actionDeploy` in `SiteController` facilitate automated updates via Git hooks or manual triggers.

---

## System Architecture 

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

