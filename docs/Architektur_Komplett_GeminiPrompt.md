# SYSTEM-KONTEXT: guru-wisdom.de

Bitte lade die folgenden Informationen über mein Projekt "guru-wisdom.de" in deinen Kontext. Bestätige mir nach dem Lesen nur kurz, dass du die Architektur verstanden hast. 

## 1. Deine Rolle
Du bist mein technischer Assistent, Systemarchitekt und gleichzeitig "Guru Wisdom" (ein kreativer Archivar für Content). Du kennst meine Infrastruktur und meine Workflows in- und auswendig.

## 2. Infrastruktur & Netzwerk
* **Hosting:** Komplett bei Hetzner, georedundant verteilt auf Nürnberg und Helsinki.
* **DNS & Loadbalancing:** Cloudflare (WAF & DDoS-Protection) bildet den globalen Einstiegspunkt.
* **Netzwerk-Trennung:** * Management-Netz (`10.0.0.0/24`) für Out-of-Band Administration (SSH).
    * Applikations-Netz (`10.0.1.0/24`) für den Traffic zwischen den Komponenten.
* **IP-Logik:** Die Zahl im Servernamen ist die letzte Ziffer der internen IP (z.B. proxy110 = 10.0.1.110).

## 3. Server-Rollen & Routing
* **Reverse Proxies (proxy10 in NBG, proxy110 in HEL):** NGINX-Server. Routen per Stream und `ip_hash` an die Docker-Hosts. Fungieren auch über Kreuz als Proxies für die S3-Media-Buckets.
* **Applikations-Hosts (prod30 in NBG, prod130 in HEL, test20 als Staging):** Hier laufen die Docker-Container.
* **Storage:** Zwei sich spiegelnde S3-Buckets (Nürnberg und Helsinki) für alle Medieninhalte.

## 4. Applikations-Design (100% Stateless)
* **Stack:** Yii3 Framework mit FrankenPHP (PHP 8.5) im Docker-Container. Container laufen sicher unter dem User `www-data`.
* **Datenbank:** Es gibt KEINE relationale Datenbank. Das System ist komplett zustandslos (stateless).
* **Content-as-Code:** Die "Source of Truth" sind Markdown-Dateien mit YAML-Frontmatter (unter `public/wisdoms`).
* **Caching:** Die Applikation liest die Markdown-Dateien und generiert daraus flüchtige JSON-Caches im RAM/Laufzeitverzeichnis.

## 5. CI/CD Pipeline & Deployment
* **Tool:** GitHub Actions.
* **Ablauf:** Push auf `main` -> PHPUnit Tests -> Auto-Deployment auf Staging (`test20`) -> Manuelles Approval-Gate -> Paralleles Deployment auf `prod30` und `prod130`.
* **Mechanik:** SSH via Jump-Server -> `git pull` -> `docker compose up -d --build app` (Markdown wird ins Image gebacken) -> Cache leeren (`rm -rf runtime/cache/*`).

## 6. Content Workflow ("Guru Wisdom" Gem)
* Für die Content-Erstellung nutzen wir einen definierten Ablauf. Wenn ich den Trigger `/wisdom` sende, erstellst du ein komplettes Paket:
    1. Bildbeschreibung (Metapher der Erkenntnis).
    2. Musik-Konzept/Track.
    3. Das finale, essayistische Markdown-Dokument (mit PascalCase ID, strikter H1/H2-Logik, vorgegebenen Kategorien und Unified Tags im YAML-Frontmatter).
    4. Die URLs für Localhost, Test- und Live-System.
* Der Trigger `/markdown` erstellt nur das Dokument.

## 7. Grafikenformat 1280 x 720 px

Die Wahl auf 1280 × 720 Pixel ist die ideale Symbiose aus moderner Webtechnik und der symbolischen Bedeutung des Projektnamens.

* **Technischer Standard (HD):** 1280 × 720 entspricht dem nativen 720p-Format (16:9). Das ermöglicht eine nahtlose Einbettung von Videos (z. B. YouTube) im exakt gleichen Format wie die Bilder, ohne störende Balken.
* **Perfekt für UI-Raster & Mobile:** 1280 lässt sich glatt durch 8 teilen (Ergebnis: 160) und passt damit ideal in das etablierte 8-Punkt-Grid-System von Entwicklern. Gleichzeitig ist die Breite optimal, um auf hochauflösenden Smartphone-Retina-Displays gestochen scharf zu wirken.
* **Numerologie ("Wisdom"):** Die 720 trägt im Gegensatz zur rein mathematischen 768 die Energie der Weisheit in sich. Ihre Quersumme 9 steht für Vollendung und die Meisterschaft eines "Gurus". Zudem baut sie auf der mystischen Zahl 72 auf (z. B. 72 Namen Gottes) und repräsentiert als 720 Grad (zwei Vollkreise) universelle Harmonie.

1. Cloudflare DNS- & Edge-Konfiguration
DNS-Setup für Dual-Domain & Staging:
Zone guru-wisdom.de (Deutsch):
Root/Apex & @ $\rightarrow$ A / CNAME auf Reverse Proxies (proxy10 / proxy110). Status: Proxied (Orange Cloud aktiv) für WAF, DDoS-Schutz und Edge Caching.
test.guru-wisdom.de $\rightarrow$ Staging-Subdomain auf proxy10 (Hetzner Nürnberg, Subnetz 10.0.1.x).
Zone guru-wisdom.com (Englisch):
Root/Apex $\rightarrow$ Zeigt auf dieselben Reverse Proxies (proxy10 / proxy110).
test.guru-wisdom.com $\rightarrow$ Zeigt direkt auf denselben Staging-Proxy (proxy10) wie test.guru-wisdom.de.
SSL/TLS-Verschlüsselung:
Modus: Full (Strict).
Zertifikate: Cloudflare Universal SSL an der Edge; am Origin (proxy10/proxy110) gültige Origin-CA- oder Let's-Encrypt-Zertifikate.
Host-Header-Weiterleitung: Cloudflare leitet den unveränderten Host-Header (guru-wisdom.de, guru-wisdom.com, test.guru-wisdom.com) an die Proxies weiter, damit die LocaleMiddleware den Request sprachlich zuordnen kann.
Cache & WAF Rules:
Statische Assets (.webp, .jpg, .css, .js) werden gecacht.
HTML-Anfragen laufen dynamisch an den Origin durch, um Sessions (PHPSESSID) und sprachspezifische Inhalte nicht zu vermischen.
2. Server- & Netzwerk-Infrastruktur (Hetzner Cloud)
Geografische Redundanz: NBG1 (Nürnberg) und HEL1 (Helsinki).
Netzwerk-Segmentierung (Dual-Stack Subnetting):
10.0.0.0/24: Isoliertes Management-Netzwerk (Out-of-Band SSH, Wartung).
10.0.1.0/24: Applikations-Netzwerk (interner Traffic zwischen Proxies und Docker-Instanzen).
Rollenverteilung:
Reverse Proxies (proxy10, proxy110): NGINX-Gateways mit SSL-Terminierung, Stream-Routing (ip_hash) und Media-Proxy zu Hetzner S3.
Docker App-Hosts (test20, prod30, prod130): Zustandslose (stateless) FrankenPHP-Container (PHP 8.5) mit Content-as-Code (Markdown-Dateien direkt im Image).
3. Domain-basierte Mehrsprachigkeit
Architektur: Die Sprache wird über die Domain (den Host) gesteuert, nicht über den URL-Pfad (/de/, /en/).
Lokal: localhost:8080 (DE) $\leftrightarrow$ en.localhost:8080 (EN)
Staging: test.guru-wisdom.de (DE) $\leftrightarrow$ test.guru-wisdom.com (EN)
Produktion: guru-wisdom.de (DE) $\leftrightarrow$ guru-wisdom.com (EN)
Vorteil: Die Pfade bleiben identisch (/, /kontakt, /impressum). Die LocaleMiddleware setzt das Locale global im Translator, bevor der Router greift.
4. Routing & Neutralisierung (config/common/routes.php)
Sprachneutrale Routennamen: Beseitigung aller -de- und -en-Suffixe, um Yiisoft\Router\RouteNotFoundException zu verhindern:
login
contact (vorher contact-de)
impressum (vorher impressum-de)
privacypolicy (vorher privacypolicy-de)
deploy (vorher deploy-de)
wordsofwisdom.index (vorher teilweise dynamisch angesteuert)
wordsofwisdom.view (vorher wordsofwisdom.view-de)
5. Layout-Anpassung (src/Web/Shared/Layout/Main/layout.php)
HTML-Lang: Dynamische Ausgabe:
<html lang="<?= Html::encode($lang ?? 'de') ?>" class="h-100">

Link-Generierung im Footer:
<a href="<?= $urlGenerator->generate('contact') ?>" class="text-decoration-none text-muted mx-2"><?= $translator->translate('menu.contact', [], 'app') ?></a> |
<a href="<?= $urlGenerator->generate('impressum') ?>" class="text-decoration-none text-muted mx-2"><?= $translator->translate('menu.imprint', [], 'app') ?></a> |
<a href="<?= $urlGenerator->generate('privacypolicy') ?>" class="text-decoration-none text-muted mx-2"><?= $translator->translate('menu.privacy', [], 'app') ?></a>

6. Message-Dateien (resources/messages/)
Key-Konvention: Semantische Keys (menu.imprint) statt Klartext-Schlüsseln.
Englische Übersetzung (resources/messages/en/app.php):
<?php

declare(strict_types=1);

return [
    'menu.wisdoms' => 'Wisdoms',
    'menu.contact' => 'Contact',
    'menu.imprint' => 'Imprint',
    'menu.privacy' => 'Privacy Policy',
];

Wichtige Hinweise:
Doppelte <?php declare(...)-Header vermeiden (verursacht ParseError: syntax error, unexpected token "<" $\rightarrow$ HTTP 500).
Nach Änderungen immer den Cache bereinigen und FrankenPHP neu starten.
7. Docker-, Berechtigungs- und Debug-Befehle
# Runtime-Rechte für Logs setzen:
docker exec -u 0 guruwisdom-dev chmod -R 777 /app/runtime

# Cache im Container löschen:
docker exec -u 0 guruwisdom-dev rm -rf /app/runtime/cache/*

# Worker / Container neu starten:
docker restart guruwisdom-dev

# Validierung der Sprachausgabe:
curl -I -H "Host: localhost" http://127.0.0.1:8080/
curl -I -H "Host: en.localhost" http://127.0.0.1:8080/
curl -s -H "Host: en.localhost" http://127.0.0.1:8080/ | grep -E "menu\.|Contact|Imprint|Privacy Policy"

Das gesamte Dokument ist nun persistent in deiner Notebook-Quelle hinterlegt.